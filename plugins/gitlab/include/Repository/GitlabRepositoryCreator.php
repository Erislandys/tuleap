<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Repository;

use Project;
use Tuleap\DB\DBTransactionExecutor;
use Tuleap\Gitlab\API\Credentials;
use Tuleap\Gitlab\API\GitlabProject;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectDao;
use Tuleap\Gitlab\Repository\Token\GitlabBotApiTokenInserter;
use Tuleap\Gitlab\Repository\Webhook\WebhookCreator;

class GitlabRepositoryCreator
{
    /**
     * @var DBTransactionExecutor
     */
    private $db_transaction_executor;

    /**
     * @var GitlabRepositoryFactory
     */
    private $gitlab_repository_factory;

    /**
     * @var GitlabRepositoryProjectDao
     */
    private $gitlab_repository_project_dao;

    /**
     * @var GitlabRepositoryDao
     */
    private $gitlab_repository_dao;

    /**
     * @var WebhookCreator
     */
    private $webhook_creator;
    /**
     * @var GitlabBotApiTokenInserter
     */
    private $token_inserter;

    public function __construct(
        DBTransactionExecutor $db_transaction_executor,
        GitlabRepositoryFactory $gitlab_repository_factory,
        GitlabRepositoryDao $gitlab_repository_dao,
        GitlabRepositoryProjectDao $gitlab_repository_project_dao,
        WebhookCreator $webhook_creator,
        GitlabBotApiTokenInserter $token_inserter
    ) {
        $this->db_transaction_executor       = $db_transaction_executor;
        $this->gitlab_repository_factory     = $gitlab_repository_factory;
        $this->gitlab_repository_dao         = $gitlab_repository_dao;
        $this->gitlab_repository_project_dao = $gitlab_repository_project_dao;
        $this->webhook_creator               = $webhook_creator;
        $this->token_inserter                = $token_inserter;
    }

    /**
     * @throws GitlabRepositoryAlreadyIntegratedInProjectException
     * @throws GitlabRepositoryWithSameNameAlreadyIntegratedInProjectException
     */
    public function integrateGitlabRepositoryInProject(
        Credentials $credentials,
        GitlabProject $gitlab_project,
        Project $project
    ): GitlabRepository {
        return $this->db_transaction_executor->execute(
            function () use ($credentials, $gitlab_project, $project) {
                $gitlab_repository_id  = $gitlab_project->getId();
                $gitlab_web_url        = $gitlab_project->getWebUrl();
                $gitlab_name_with_path = $gitlab_project->getPathWithNamespace();

                if (
                    $this->gitlab_repository_dao->isAGitlabRepositoryWithSameNameAlreadyIntegratedInProject(
                        $gitlab_name_with_path,
                        $gitlab_web_url,
                        (int) $project->getID()
                    )
                ) {
                    throw new GitlabRepositoryWithSameNameAlreadyIntegratedInProjectException($gitlab_name_with_path);
                }

                $already_existing_gitlab_repository = $this->gitlab_repository_factory->getGitlabRepositoryByGitlabRepositoryIdAndPath(
                    $gitlab_repository_id,
                    $gitlab_web_url
                );
                if ($already_existing_gitlab_repository !== null) {
                    $this->addAlreadyIntegratedGitlabRepositoryInProject(
                        $already_existing_gitlab_repository,
                        $project
                    );

                    return $already_existing_gitlab_repository;
                }

                return $this->createGitlabRepositoryIntegration(
                    $credentials,
                    $gitlab_project,
                    $project
                );
            }
        );
    }

    /**
     * @throws GitlabRepositoryAlreadyIntegratedInProjectException
     */
    private function addAlreadyIntegratedGitlabRepositoryInProject(
        GitlabRepository $already_existing_gitlab_repository,
        Project $project
    ): void {
        $repository_id = $already_existing_gitlab_repository->getId();
        $project_id    = (int) $project->getID();


        if ($this->gitlab_repository_project_dao->isGitlabRepositoryIntegratedInProject($repository_id, $project_id)) {
            throw new GitlabRepositoryAlreadyIntegratedInProjectException(
                $repository_id,
                $project_id
            );
        }

        $this->gitlab_repository_project_dao->addGitlabRepositoryIntegrationInProject(
            $repository_id,
            $project_id
        );
    }

    private function createGitlabRepositoryIntegration(
        Credentials $credentials,
        GitlabProject $gitlab_project,
        Project $project
    ): GitlabRepository {
        $id = $this->gitlab_repository_dao->createGitlabRepository(
            $gitlab_project->getId(),
            $gitlab_project->getPathWithNamespace(),
            $gitlab_project->getDescription(),
            $gitlab_project->getWebUrl(),
            $gitlab_project->getLastActivityAt()->getTimestamp(),
        );

        $gitlab_repository = $this->gitlab_repository_factory->getGitlabRepositoryByGitlabProjectAndId(
            $gitlab_project,
            $id
        );

        $this->gitlab_repository_project_dao->addGitlabRepositoryIntegrationInProject(
            $id,
            (int) $project->getID()
        );

        $this->webhook_creator->generateWebhookInGitlabProject($credentials, $gitlab_repository);

        $this->token_inserter->insertToken($gitlab_repository, $credentials->getBotApiToken());

        return $gitlab_repository;
    }
}

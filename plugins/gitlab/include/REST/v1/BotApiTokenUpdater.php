<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\REST\v1;

use GitPermissionsManager;
use Luracast\Restler\RestException;
use Psr\Log\LoggerInterface;
use Tuleap\Gitlab\API\Credentials;
use Tuleap\Gitlab\API\GitlabProjectBuilder;
use Tuleap\Gitlab\API\GitlabRequestException;
use Tuleap\Gitlab\API\GitlabResponseAPIException;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\GitlabRepositoryFactory;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectRetriever;
use Tuleap\Gitlab\Repository\Token\GitlabBotApiTokenInserter;
use Tuleap\Gitlab\Repository\Webhook\WebhookCreator;
use Tuleap\REST\I18NRestException;

class BotApiTokenUpdater
{
    /**
     * @var GitlabProjectBuilder
     */
    private $project_builder;
    /**
     * @var GitlabRepositoryFactory
     */
    private $repository_factory;
    /**
     * @var GitlabRepositoryProjectRetriever
     */
    private $project_retriever;
    /**
     * @var GitPermissionsManager
     */
    private $permissions_manager;
    /**
     * @var GitlabBotApiTokenInserter
     */
    private $bot_api_token_inserter;
    /**
     * @var WebhookCreator
     */
    private $webhook_creator;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GitlabRepositoryFactory $repository_factory,
        GitlabProjectBuilder $project_builder,
        GitlabRepositoryProjectRetriever $project_retriever,
        GitPermissionsManager $permissions_manager,
        GitlabBotApiTokenInserter $bot_api_token_inserter,
        WebhookCreator $webhook_creator,
        LoggerInterface $logger
    ) {
        $this->project_builder        = $project_builder;
        $this->repository_factory     = $repository_factory;
        $this->project_retriever      = $project_retriever;
        $this->permissions_manager    = $permissions_manager;
        $this->bot_api_token_inserter = $bot_api_token_inserter;
        $this->webhook_creator        = $webhook_creator;
        $this->logger                 = $logger;
    }

    public function update(ConcealedBotApiTokenPatchRepresentation $patch_representation, \PFUser $current_user): void
    {
        $repository = $this->repository_factory->getGitlabRepositoryByGitlabRepositoryIdAndPath(
            $patch_representation->gitlab_repository_id,
            $patch_representation->gitlab_repository_url,
        );
        if (! $repository) {
            throw new RestException(404);
        }

        if (! $this->isUserAllowedToUpdateBotApiToken($current_user, $repository)) {
            throw new RestException(404);
        }

        $credentials = new Credentials($repository->getGitlabServerUrl(), $patch_representation->gitlab_bot_api_token);

        try {
            $this->project_builder->getProjectFromGitlabAPI($credentials, $repository->getGitlabRepositoryId());
            $this->webhook_creator->generateWebhookInGitlabProject($credentials, $repository);
            $this->bot_api_token_inserter->insertToken($repository, $patch_representation->gitlab_bot_api_token);
        } catch (GitlabRequestException $e) {
            $this->logger->error('Unable to update the token', ['exception' => $e]);

            throw new I18NRestException(
                400,
                sprintf(
                    dgettext(
                        'tuleap-gitlab',
                        'Unable to contact the server with the provided token. Please ensure that token has "api" scope. GitLab server error: %s'
                    ),
                    $e->getGitlabServerMessage()
                )
            );
        } catch (GitlabResponseAPIException $e) {
            $this->logger->error('Unable to update the token', ['exception' => $e]);

            throw new I18NRestException(
                500,
                dgettext(
                    'tuleap-gitlab',
                    "We managed to contact the server, but couldn't parse the response. We are not confident enough to update the token."
                )
            );
        }
    }

    private function isUserAllowedToUpdateBotApiToken(\PFUser $current_user, GitlabRepository $repository): bool
    {
        foreach ($this->project_retriever->getProjectsGitlabRepositoryIsIntegratedIn($repository) as $project) {
            if ($this->permissions_manager->userIsGitAdmin($current_user, $project)) {
                return true;
            }
        }

        return false;
    }
}

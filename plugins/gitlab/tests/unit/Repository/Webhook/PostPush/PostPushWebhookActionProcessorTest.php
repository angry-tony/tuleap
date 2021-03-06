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

namespace Tuleap\Gitlab\Repository\Webhook\PostPush;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Project;
use Psr\Log\LoggerInterface;
use Reference;
use ReferenceManager;
use Tuleap\Gitlab\Reference\TuleapReferenceRetriever;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectRetriever;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceDAO;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferencedArtifactNotFoundException;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceNotFoundException;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferencesParser;

final class PostPushWebhookActionProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PostPushWebhookActionProcessor
     */
    private $processor;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|LoggerInterface
     */
    private $logger;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|GitlabRepositoryProjectRetriever
     */
    private $gitlab_repository_project_retriever;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ReferenceManager
     */
    private $reference_manager;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|CommitTuleapReferenceDAO
     */
    private $commit_tuleap_reference_dao;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TuleapReferenceRetriever
     */
    private $tuleap_reference_retriever;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->gitlab_repository_project_retriever = Mockery::mock(GitlabRepositoryProjectRetriever::class);
        $this->commit_tuleap_reference_dao         = Mockery::mock(CommitTuleapReferenceDAO::class);
        $this->reference_manager                   = Mockery::mock(ReferenceManager::class);
        $this->tuleap_reference_retriever          = Mockery::mock(TuleapReferenceRetriever::class);

        $this->processor = new PostPushWebhookActionProcessor(
            new CommitTuleapReferencesParser(),
            $this->gitlab_repository_project_retriever,
            $this->commit_tuleap_reference_dao,
            $this->reference_manager,
            $this->tuleap_reference_retriever,
            $this->logger
        );
    }

    public function testItProcessesActionsForPostPushWebhook(): void
    {
        $gitlab_repository = new GitlabRepository(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new DateTimeImmutable()
        );

        $webhook_data = new PostPushWebhookData(
            'push',
            123654,
            'https://example.com/root/repo01',
            [
                new PostPushCommitWebhookData(
                    'feff4ced04b237abb8b4a50b4160099313152c3c',
                    'A commit with three references, two bad, one good',
                    'A commit with three references: TULEAP-666 TULEAP-777 TULEAP-123',
                    1608110510,
                    "john-snow@the-wall.com",
                    "John Snow"
                )
            ]
        );

        $this->gitlab_repository_project_retriever->shouldReceive('getProjectsGitlabRepositoryIsIntegratedIn')
            ->once()
            ->with($gitlab_repository)
            ->andReturn([
                Project::buildForTest()
            ]);

        $this->tuleap_reference_retriever->shouldReceive('retrieveTuleapReference')
            ->once()
            ->with(666)
            ->andThrow(new CommitTuleapReferencedArtifactNotFoundException(666));

        $this->tuleap_reference_retriever->shouldReceive('retrieveTuleapReference')
            ->once()
            ->with(777)
            ->andThrow(new CommitTuleapReferenceNotFoundException());

        $this->tuleap_reference_retriever->shouldReceive('retrieveTuleapReference')
            ->once()
            ->with(123)
            ->andReturn(
                new Reference(
                    0,
                    'key',
                    'desc',
                    'link',
                    'P',
                    'service_short_name',
                    'nature',
                    1,
                    100
                )
            );

        $this->logger
            ->shouldReceive('info')
            ->with("3 Tuleap references found in commit feff4ced04b237abb8b4a50b4160099313152c3c")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|_ Reference to Tuleap artifact #123 found.")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|_ Reference to Tuleap artifact #666 found.")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|_ Reference to Tuleap artifact #777 found.")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|  |_ Tuleap artifact #123 found, cross-reference will be added for each project the GitLab repository is integrated in.")
            ->once();
        $this->logger
            ->shouldReceive('error')
            ->with('Tuleap artifact #666 not found, no cross-reference will be added.')
            ->once();
        $this->logger
            ->shouldReceive('error')
            ->with("No reference found with the keyword 'art', and this must not happen. If you read this, this is really bad.")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("Commit data for feff4ced04b237abb8b4a50b4160099313152c3c saved in database")
            ->once();

        $this->reference_manager->shouldReceive('insertCrossReference')->once();
        $this->commit_tuleap_reference_dao->shouldReceive('saveGitlabCommitInfo')
            ->once()
            ->with(
                1,
                'feff4ced04b237abb8b4a50b4160099313152c3c',
                1608110510,
                'A commit with three references, two bad, one good',
                'John Snow',
                'john-snow@the-wall.com'
            );

        $this->processor->process($gitlab_repository, $webhook_data);
    }

    public function testItDoesNothingIfArtifactDoesNotExist(): void
    {
        $gitlab_repository = new GitlabRepository(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new DateTimeImmutable()
        );

        $webhook_data = new PostPushWebhookData(
            'push',
            123654,
            'https://example.com/root/repo01',
            [
                new PostPushCommitWebhookData(
                    'feff4ced04b237abb8b4a50b4160099313152c3c',
                    'commit TULEAP-123 01',
                    'commit TULEAP-123 01',
                    1608110510,
                    "john-snow@the-wall.com",
                    "John Snow"
                )
            ]
        );

        $this->tuleap_reference_retriever->shouldReceive('retrieveTuleapReference')
            ->once()
            ->with(123)
            ->andThrow(new CommitTuleapReferencedArtifactNotFoundException(123));

        $this->gitlab_repository_project_retriever->shouldReceive('getProjectsGitlabRepositoryIsIntegratedIn')
            ->once()
            ->with($gitlab_repository)
            ->andReturn([
                Project::buildForTest()
            ]);

        $this->commit_tuleap_reference_dao->shouldReceive('saveGitlabCommitInfo')
            ->never();

        $this->logger
            ->shouldReceive('info')
            ->with("1 Tuleap references found in commit feff4ced04b237abb8b4a50b4160099313152c3c")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|_ Reference to Tuleap artifact #123 found.")
            ->once();
        $this->logger
            ->shouldReceive('error')
            ->with("Tuleap artifact #123 not found, no cross-reference will be added.")
            ->once();

        $this->reference_manager->shouldNotReceive('insertCrossReference');

        $this->processor->process($gitlab_repository, $webhook_data);
    }

    public function testItDoesNothingIfArtReferenceNotFound(): void
    {
        $gitlab_repository = new GitlabRepository(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new DateTimeImmutable()
        );

        $webhook_data = new PostPushWebhookData(
            'push',
            123654,
            'https://example.com/root/repo01',
            [
                new PostPushCommitWebhookData(
                    'feff4ced04b237abb8b4a50b4160099313152c3c',
                    'commit TULEAP-123 01',
                    'commit TULEAP-123 01',
                    1608110510,
                    "john-snow@the-wall.com",
                    "John Snow"
                )
            ]
        );

        $this->tuleap_reference_retriever->shouldReceive('retrieveTuleapReference')
            ->once()
            ->with(123)
            ->andThrow(new CommitTuleapReferenceNotFoundException());

        $this->gitlab_repository_project_retriever->shouldReceive('getProjectsGitlabRepositoryIsIntegratedIn')
            ->once()
            ->with($gitlab_repository)
            ->andReturn([
                Project::buildForTest()
            ]);

        $this->commit_tuleap_reference_dao->shouldReceive('saveGitlabCommitInfo')
            ->never();

        $this->logger
            ->shouldReceive('info')
            ->with("1 Tuleap references found in commit feff4ced04b237abb8b4a50b4160099313152c3c")
            ->once();
        $this->logger
            ->shouldReceive('info')
            ->with("|_ Reference to Tuleap artifact #123 found.")
            ->once();
        $this->logger
            ->shouldReceive('error')
            ->with("No reference found with the keyword 'art', and this must not happen. If you read this, this is really bad.")
            ->once();

        $this->reference_manager->shouldNotReceive('insertCrossReference');

        $this->processor->process($gitlab_repository, $webhook_data);
    }
}

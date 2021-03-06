<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\Reference;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class CrossReferenceByDirectionPresenterBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    private $event_dispatcher;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\ReferenceManager
     */
    private $reference_manager;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|CrossReferencePresenterFactory
     */
    private $factory;
    /**
     * @var CrossReferenceByDirectionPresenterBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->event_dispatcher  = Mockery::mock(EventDispatcherInterface::class);
        $this->reference_manager = Mockery::mock(\ReferenceManager::class);
        $this->factory           = Mockery::mock(CrossReferencePresenterFactory::class);

        $this->builder = new CrossReferenceByDirectionPresenterBuilder(
            $this->event_dispatcher,
            $this->reference_manager,
            $this->factory,
        );
    }

    public function testBuild()
    {
        $user = Mockery::mock(\PFUser::class);

        $this->factory
            ->shouldReceive('getSourcesOfEntity')
            ->with("PageName", "wiki", 102)
            ->once()
            ->andReturn([]);
        $this->factory
            ->shouldReceive('getTargetsOfEntity')
            ->with("PageName", "wiki", 102)
            ->once()
            ->andReturn([]);

        $available_natures = [
            new Nature('git', '', 'Git'),
            new Nature('wiki', '', 'Wiki'),
        ];

        $this->reference_manager
            ->shouldReceive('getAvailableNatures')
            ->andReturn($available_natures);

        $organizer = Mockery::mock(CrossReferenceByNatureOrganizer::class);

        $this->event_dispatcher
            ->shouldReceive('dispatch')
            ->with(Mockery::type(CrossReferenceByNatureOrganizer::class))
            ->andReturn($organizer);

        $organizer->shouldReceive('organizeRemainingCrossReferences')->twice();

        $organizer->shouldReceive('getNatures')->twice()->andReturn([]);

        $presenter = $this->builder->build("PageName", "wiki", 102, $user);

        self::assertEquals([], $presenter->sources_by_nature);
        self::assertEquals([], $presenter->targets_by_nature);
        self::assertFalse($presenter->has_target);
        self::assertFalse($presenter->has_source);
    }
}

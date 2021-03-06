<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;

/**
 * Setup configuration using handler.
 */
class SetupConfigurationStep implements TestStepInterface
{
    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Admin cache page.
     *
     * @var AdminCache
     */
    private $adminCache;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Rollback.
     *
     * @var bool
     */
    protected $rollback;

    /**
     * Flush cache.
     *
     * @var bool
     */
    protected $flushCache;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param AdminCache $adminCache
     * @param string $configData
     * @param bool $rollback
     * @param bool $flushCache
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        AdminCache $adminCache,
        $configData = null,
        $rollback = false,
        $flushCache = false
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->adminCache = $adminCache;
        $this->configData = $configData;
        $this->rollback = $rollback;
        $this->flushCache = $flushCache;
    }

    /**
     * Set config.
     *
     * @return array
     */
    public function run()
    {
        if ($this->configData === null) {
            return [];
        }
        $prefix = ($this->rollback == false) ? '' : '_rollback';

        $configData = array_map('trim', explode(',', $this->configData));
        $result = [];

        foreach ($configData as $configDataSet) {
            $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $configDataSet . $prefix]);
            if ($config->hasData('section')) {
                $config->persist();
                $result[] = $config;
            }
        }
        
        if ($this->flushCache) {
            $this->adminCache->open();
            $this->adminCache->getActionsBlock()->flushMagentoCache();
            $this->adminCache->getMessagesBlock()->waitSuccessMessage();
        }

        return ['config' => $result];
    }

    /**
     * Rollback configuration.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->rollback = true;
        $this->run();
    }
}

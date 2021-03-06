<?php
/**
 * \Magento\Widget\Model\Config\Data
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Widget\Model\Config;

/**
 * @magentoDataFixture Magento/Adminhtml/controllers/_files/cache/all_types_disabled.php
 * @magentoAppArea adminhtml
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data
     */
    protected $_configData;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Core\Model\Dir $dirs */
        $dirs = $objectManager->create(
            'Magento\Core\Model\Dir', array(
                'baseDir' => BP,
                'dirs' => array(
                    \Magento\Core\Model\Dir::MODULES => __DIR__ . '/_files/code',
                    \Magento\Core\Model\Dir::CONFIG => __DIR__ . '/_files/code',
                    \Magento\Core\Model\Dir::THEMES => __DIR__ . '/_files/design',
                )
            )
        );

        /** @var \Magento\Core\Model\Module\Declaration\FileResolver $modulesDeclarations */
        $modulesDeclarations = $objectManager->create(
            'Magento\Core\Model\Module\Declaration\FileResolver', array(
                'applicationDirs' => $dirs,
            )
        );


        /** @var \Magento\Core\Model\Module\Declaration\Reader\Filesystem $filesystemReader */
        $filesystemReader = $objectManager->create(
            'Magento\Core\Model\Module\Declaration\Reader\Filesystem', array(
                'fileResolver' => $modulesDeclarations,
            )
        );

        /** @var \Magento\Core\Model\ModuleList $modulesList */
        $modulesList = $objectManager->create(
            'Magento\Core\Model\ModuleList', array(
                'reader' => $filesystemReader,
            )
        );

        /** @var \Magento\Core\Model\Config\Modules\Reader $moduleReader */
        $moduleReader = $objectManager->create(
            'Magento\Core\Model\Config\Modules\Reader', array(
                'moduleList' => $modulesList
            )
        );
        $moduleReader->setModuleDir('Magento_Test', 'etc', __DIR__ . '/_files/code/Magento/Test/etc');

        /** @var \Magento\Widget\Model\Config\FileResolver $fileResolver */
        $fileResolver = $objectManager->create(
            'Magento\Widget\Model\Config\FileResolver', array(
                'moduleReader' => $moduleReader,
                'applicationDirs' => $dirs,
            )
        );

        $schema = __DIR__ . '/../../../../../../../../app/code/Magento/Widget/etc/widget.xsd';
        $perFileSchema = __DIR__ . '/../../../../../../../../app/code/Magento/Widget/etc/widget_file.xsd';
        $reader = $objectManager->create(
            'Magento\Widget\Model\Config\Reader', array(
                'moduleReader' => $moduleReader,
                'fileResolver' => $fileResolver,
                'schema' => $schema,
                'perFileSchema' => $perFileSchema,
            )
        );

        $this->_configData = $objectManager->create('Magento\Widget\Model\Config\Data', array(
            'reader' => $reader,
        ));
    }

    public function testGet()
    {
        $result = $this->_configData->get();
        $expected = include '_files/expectedGlobalDesignArray.php';
        $this->assertEquals($expected, $result);
    }
}

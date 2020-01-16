<?php
namespace RKW\RkwNewsletter\Tests\Functional\Controller;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Service\MailService;
use RKW\RkwNewsletter\Controller\SubscriptionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * NewsletterCommandControllerTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewsletterCommandControllerTest extends FunctionalTestCase
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_newsletter',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['css_styled_content'];

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->importDataSet(__DIR__ . '/Fixtures/Database/ProcessApprovalsCommand.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:css_styled_content/static/constants.txt',
                'EXT:css_styled_content/static/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.txt',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Tests/Functional/Controller/Fixtures/Frontend/Basics.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManager->get(SubscriptionController::class);

    }


    /**
     * @test
     */
    public function processApprovalsCommandIssueProcessing ()
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \RKW\RkwNewsletter\Helper\Approval $approval */
        $approval = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Approval');
        $approval->sendInfoAndReminderMailsForApprovals();

    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}
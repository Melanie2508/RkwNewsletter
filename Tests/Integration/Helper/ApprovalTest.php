<?php
namespace RKW\RkwNewsletter\Tests\Integration\Helper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * ApprovalTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     */
    private $approvalRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/Database/ProcessApprovalsCommand.xml');

        /*
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Utility/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        */

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);

    }


    /**
     * @test
     */
    public function sendInfoAndReminderMailsForApprovals_FetchesGivenApprovalOfAnIssue_ReturnsTrue()
    {
        // ts data fetched from rkw-kompetenzzentrum.de
        $reminderApprovalStage1 = 302400;
        $reminderApprovalStage2 = 302400;

        $openApprovalList = $this->approvalRepository->findAllOpenApprovalsByTime(intval($reminderApprovalStage1), intval($reminderApprovalStage2))->toArray();

        static::assertCount(1, $openApprovalList);
    }


    /**
     * @test
     */
    public function sendInfoAndReminderMailsForApprovals_SendReminderMailOnStage1_ReturnsTrue()
    {
        // ts data fetched from rkw-kompetenzzentrum.de
        $reminderApprovalStage1 = 302400;
        $reminderApprovalStage2 = 302400;

        // not a newsletter!
        $openApprovalList = $this->approvalRepository->findAllOpenApprovalsByTime(intval($reminderApprovalStage1), intval($reminderApprovalStage2));

        if (count($openApprovalList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
            foreach ($openApprovalList as $approval) {

                // check if Issue to Approval exists
                static::assertObjectHasAttribute('title', $approval->getIssue());

                // Case 1: infomail at stage 1
                $approvalAdmins = [];
                $stage = 1;
                $isReminder = false;
                if (
                    ($approval->getAllowedTstampStage1() < 1)
                    && ($approval->getSentInfoTstampStage1() < 1)
                ) {

                    if (count($approval->getTopic()->getApprovalStage1()) > 0) {
                        $approval->setSentInfoTstampStage1(time());
                        $approvalAdmins = $approval->getTopic()->getApprovalStage1()->toArray();
                    }

                }
            }

            // dummy
            //static::assertTrue(1);
        }
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
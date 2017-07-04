<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Course\Service\ThreadService;
use Biz\Task\Service\TaskResultService;
use Biz\Course\Service\CourseNoteService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function summary($courseId)
    {
        $summary = array(
            'studentNum' => 0,
            'noteNum' => 0,
            'askNum' => 0,
            'discussionNum' => 0,
            'finishedNum' => 0, //完成人数
        );

        $summary['studentNum'] = $this->getCourseMemberService()->countMembers(array('courseId' => $courseId, 'role' => 'student'));
        $summary['noteNum'] = $this->getCourseNoteService()->countCourseNotes(array('courseId' => $courseId));
        $summary['askNum'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'question'));
        $summary['discussionNum'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'discussion'));
        $summary['finishedNum'] = $this->countMembersFinishedAllTasksByCourseId($courseId);
        $summary['finishedRate'] = $this->getPercent($summary['finishedNum'], $summary['studentNum']);

        return $summary;
    }

    public function getLateMonthLearnData($courseId)
    {
        $now = time();
        $lastMonthData = $this->getLatestMonthData($courseId, $now);
        $before30DaysData = $this->getAMonthAgoStatCount($courseId, $now);
        $late30DaysStat = array();
        for ($i = 29; $i >= 0; --$i) {
            $day = date('Y-m-d', strtotime('-'.$i.' days'));
            $late30DaysStat[$day]['day'] = date('m-d', strtotime('-'.$i.' days'));
            $late30DaysStat[$day]['studentNum'] = $before30DaysData['studentNum'];
            $late30DaysStat[$day]['finishedNum'] = $before30DaysData['finishedNum'];
            $late30DaysStat[$day]['finishedRate'] = $before30DaysData['finishedRate'];
            $late30DaysStat[$day]['noteNum'] = $before30DaysData['noteNum'];
            $late30DaysStat[$day]['askNum'] = $before30DaysData['askNum'];
            $late30DaysStat[$day]['discussionNum'] = $before30DaysData['discussionNum'];
        }

        //隐藏笔记、提问、讨论的历史数据
        $this->countStudentsData($courseId, $lastMonthData['students'], $late30DaysStat);

        return $late30DaysStat;
    }

    public function getCourseTaskLearnStat($courseId)
    {
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);

        foreach ($tasks as &$task) {
            if ($task['status'] !== 'published') {
                continue;
            }

            $task['alias'] = $task['number'] ? '任务'.$task['number'] : '选修任务';

            $task['finishedNum'] = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($task['id'], 'finish');
            $task['learnNum'] = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($task['id'], 'start');
            $task['finishedRate'] = $this->getPercent($task['finishedNum'], $task['learnNum'] + $task['finishedNum']);
        }

        return array_reverse($tasks);
    }

    private function countMembersFinishedAllTasksByCourseId($courseId, $finishedTimeLessThan = '')
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $condition = array(
            'role' => 'student',
            'learnedCompulsoryTaskNumGreaterThan' => $course['compulsoryTaskNum'],
            'courseId' => $courseId,
        );

        if (!empty($finishedTimeLessThan)) {
            $condition['lastLearnTime_LE'] = $finishedTimeLessThan;
        }
        $memberCount = $this->getCourseMemberService()->countMembers($condition);

        return $memberCount;
    }

    /**
     * 获取30天以前的数据.
     */
    private function getAMonthAgoStatCount($courseId, $now)
    {
        $role = 'student';
        $startTimeLessThan = strtotime('- 29 days', $now);
        $result = array();

        //学员数
        $result['studentNum'] = $this->getCourseMemberService()->countMembers(array(
            'courseId' => $courseId,
            'role' => $role,
            'startTimeLessThan' => $startTimeLessThan,
        ));

        //完成数
        $result['finishedNum'] = $this->countMembersFinishedAllTasksByCourseId($courseId, $startTimeLessThan);

        //完成率
        $result['finishedRate'] = $this->getPercent($result['finishedNum'], $result['studentNum']);

        //笔记数
        $result['noteNum'] = $this->getCourseNoteService()->countCourseNotes(array(
            'courseId' => $courseId,
            'startTimeLessThan' => $startTimeLessThan,
        ));

        //问题数
        $result['askNum'] = $this->getThreadService()->countThreads(array(
            'courseId' => $courseId,
            'type' => 'question',
            'startTimeLessThan' => $startTimeLessThan,
        ));

        //讨论数
        $result['discussionNum'] = $this->getThreadService()->countThreads(array(
            'courseId' => $courseId,
            'type' => 'discussion',
            'startTimeLessThan' => $startTimeLessThan,
        ));

        return $result;
    }

    private function countStudentsData($courseId, $students, &$late30DaysStat)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        foreach ($students as $student) {
            $student['createdDay'] = date('Y-m-d', $student['createdTime']);
            $student['finishedDay'] = date('Y-m-d', $student['lastLearnTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($student['createdDay']) <= strtotime($day)) {
                    ++$stat['studentNum'];
                }

                if ($student['learnedCompulsoryTaskNum'] >= $course['compulsoryTaskNum'] && ($student['lastLearnTime'] <= strtotime($day))) {
                    ++$stat['finishedNum'];
                }
            }
        }

        foreach ($late30DaysStat as $day => &$stat) {
            $stat['finishedRate'] = $this->getPercent($stat['finishedNum'], $stat['studentNum']);
        }
    }

    private function countNotesData($notes, &$late30DaysStat)
    {
        foreach ($notes as $note) {
            $note['createdDay'] = date('Y-m-d', $note['createdTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($note['createdDay']) <= strtotime($day)) {
                    ++$stat['noteNum'];
                }
            }
        }
    }

    private function countAsksData($asks, &$late30DaysStat)
    {
        foreach ($asks as $ask) {
            $ask['createdDay'] = date('Y-m-d', $ask['createdTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($ask['createdDay']) <= strtotime($day)) {
                    ++$stat['askNum'];
                }
            }
        }
    }

    private function countDiscussionsData($discussions, &$late30DaysStat)
    {
        foreach ($discussions as $discussion) {
            $discussion['createdDay'] = date('Y-m-d', $discussion['createdTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($discussion['createdDay']) <= strtotime($day)) {
                    ++$stat['discussionNum'];
                }
            }
        }
    }

    /**
     * [getLatestMonthData 获取最近一个月的数据].
     */
    private function getLatestMonthData($courseId, $now)
    {
        $startTimeGreaterThan = strtotime('- 29 days', $now);
        $role = 'student';
        $result = array();

        $students = $this->getCourseMemberService()->searchMembers(
            array(
                'courseId' => $courseId,
                'role' => $role,
                'startTimeGreaterThan' => $startTimeGreaterThan,
            ),
            array('createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $result['students'] = $students;

        $result['notes'] = $this->getCourseNoteService()->searchNotes(
            array(
                'courseId' => $courseId,
                'startTimeGreaterThan' => $startTimeGreaterThan,
            ),
            array('createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $result['asks'] = $this->getThreadService()->searchThreads(
            array(
                'courseId' => $courseId,
                'type' => 'question',
                'startTimeGreaterThan' => $startTimeGreaterThan,
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        $result['discussions'] = $this->getThreadService()->searchThreads(
            array(
                'courseId' => $courseId,
                'type' => 'discussion',
                'startTimeGreaterThan' => $startTimeGreaterThan,
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        return $result;
    }

    private function getPercent($count, $total)
    {
        $percent = $total == 0 ? 0 : round($count / $total, 3) * 100;

        return $percent > 100 ? 100 : $percent;
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}

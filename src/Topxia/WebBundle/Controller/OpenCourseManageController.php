<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseManageController extends BaseController
{
    public function liveOpenTimeSetAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if ($request->getMethod() == 'POST') {
            $liveLesson              = $request->request->all();
            $liveLesson['type']      = 'liveOpen';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['length']    = $liveLesson['timeLength'];
            $liveLesson['title']     = $course['title'];

            $speakerId = current($liveCourse['teacherIds']);
            $speaker   = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
            $speaker   = $speaker ? $speaker['nickname'] : '老师';

            $liveLogo    = $this->getSettingService()->get('course');
            $liveLogoUrl = "";

            if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
                $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
            }

            $client = new EdusohoLiveClient();
            $live   = $client->createLive(array(
                'summary'     => $liveLesson['summary'],
                'title'       => $liveLesson['title'],
                'speaker'     => $speaker,
                'startTime'   => $liveLesson['startTime'].'',
                'endTime'     => ($liveLesson['startTime'] + $liveLesson['length'] * 60).'',
                'authUrl'     => $this->generateUrl('live_auth', array(), true),
                'jumpUrl'     => $this->generateUrl('live_jump', array('id' => $liveLesson['courseId']), true),
                'liveLogoUrl' => $liveLogoUrl
            ));

            if (empty($live)) {
                throw new \RuntimeException('创建直播教室失败，请重试！');
            }

            if (isset($live['error'])) {
                throw new \RuntimeException($live['error']);
            }

            $liveLesson['mediaId']      = $live['id'];
            $liveLesson['liveProvider'] = $live['provider'];
            $liveLesson                 = $this->getCourseService()->createLesson($liveLesson);
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course' => $course
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-marketing.html.twig', array(
            'course' => $course
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}

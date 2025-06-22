<?php

declare(strict_types=1);

namespace KejawenLab\Application\SemartHris\Controller\Front;

use KejawenLab\Application\SemartHris\Entity\LiveAttendance;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/attendance/live")
 */
class LiveAttendanceController extends Controller
{
    /**
     * @Route("/", name="live_attendance")
     */
    public function index(): Response
    {
        return $this->render('app/attendance/live.html.twig');
    }

    /**
     * @Route("/submit", name="submit_live_attendance", methods={"POST"})
     */
    public function submit(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['image'])) {
            return new JsonResponse(['status' => 'error'], 400);
        }

        $image = preg_replace('#^data:image/\w+;base64,#i', '', $data['image']);
        $image = base64_decode($image);
        $fileName = sprintf('%s_%s.png', $this->getUser()->getId(), (new \DateTime())->format('YmdHis'));
        $dir = $this->getParameter('kernel.project_dir').'/public/files/live';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(sprintf('%s/%s', $dir, $fileName), $image);

        $live = new LiveAttendance();
        $live->setEmployee($this->getUser());
        $live->setCapturedAt(new \DateTime());
        $live->setType($data['type'] ?? 'in');
        $live->setPhotoPath('/files/live/'.$fileName);
        $live->setLatitude($data['lat'] ?? 0);
        $live->setLongitude($data['lon'] ?? 0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($live);
        $em->flush();

        return new JsonResponse(['status' => 'ok']);
    }
}

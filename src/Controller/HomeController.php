<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WorkCalendar;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $request = Request::createFromGlobals();
        $uri = $request->headers->get('host');

        return new Response(
            'Please send a GET request with a valid year e.g. "' . $uri . '/2024"'
        );
    }

    #[Route('/{year}', name: 'paydaySchedule', methods: ['GET'])]
    public function paydaySchedule($year, WorkCalendar $workCalendar, ValidatorInterface $validator): JsonResponse
    {
        $errors = $this->validateRawValues($year, $validator);

        if ($errors->count()) {
            $errorMessage = $errors[0]->getMessage();
            return new JsonResponse($errorMessage, 400);
        }

        $paydayParam =  $this->getParameter('app.payday.day_of_month');

        return new JsonResponse(
            json_encode(
                $workCalendar->getPaydaySchedule(
                    $year,
                    $paydayParam,
                )
            )
        );
    }

    private function validateRawValues($year, ValidatorInterface $validator)
    {
        $rangeConstraint = new Assert\Range(
            [
                'min' => 2000,
                'max' => 2999,
                'notInRangeMessage' => 'Please schedule all payments between {{ min }} and {{ max }}',
            ]
        );

        return $validator->validate(
            $year,
            $rangeConstraint
        );
    }
}

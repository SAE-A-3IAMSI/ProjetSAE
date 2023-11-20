<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController
{
    #[Route('/', name: 'app_lucky_number')]
    public function number(): Response
    {
        $number = 2;

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }
}
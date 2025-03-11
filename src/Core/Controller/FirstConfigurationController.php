<?php

namespace App\Core\Controller;

use App\Core\Service\System\WebConfigurator\WebConfiguratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstConfigurationController extends AbstractController
{
    public function __construct(
        private readonly WebConfiguratorService $webConfiguratorService,
    ) {}

    #[Route('/first-configuration', name: 'first_configuration')]
    public function index(
        Request $request,
    ): Response
    {
        $this->validateConfiguratorAccess();

        return $this->render(
            '@core_templates/configurator/configurator.html.twig',
            $this->webConfiguratorService->getDataForFirstConfiguration($request),
        );
    }

    #[Route('/first-configuration/validate-step', name: 'first_configuration_validate_step', methods: ['POST'])]
    public function validateStep(
        Request $request,
    ): JsonResponse
    {
        $this->validateConfiguratorAccess();

        $isStepValidated = $this->webConfiguratorService->validateStep($request->request->all());
        $responseStatus = $isStepValidated->isVerificationSuccessful ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(
            data: $isStepValidated->errorMessage,
            status: $responseStatus,
        );
    }

    #[Route('/first-configuration/finish', name: 'first_configuration_finish', methods: ['POST'])]
    public function finishConfiguration(
        Request $request,
    ): JsonResponse
    {
        $this->validateConfiguratorAccess();

        $isSuccessfulFinished = $this->webConfiguratorService->finishConfiguration($request->request->all());
        $responseStatus = $isSuccessfulFinished->isVerificationSuccessful ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(
            data: $isSuccessfulFinished->errorMessage,
            status: $responseStatus
        );
    }

    private function validateConfiguratorAccess(): void
    {
        $isConfiguratorEnabled = $this->webConfiguratorService->isConfiguratorEnabled();

        if (!$isConfiguratorEnabled || $this->getUser()) {
            throw $this->createNotFoundException();
        }
    }
}

<?php

namespace App\Controller\Administrators;

use App\Controller\AppController;
use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use GuzzleHttp\Psr7\UploadedFile;

/**
 * Callas Controller
 *
 * @property CallasCommands $callas
 */
class CallasController extends AppController
{
    private $callas;

    /**
     * @return \Cake\Http\Response|void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->callas = new CallasCommands();
    }


    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        try {
            $cliVersion = $this->callas->getCliVersion();
            $cliStatus = $this->callas->getCliStatus();

            if (!$cliVersion) {
                $cliVersion = 'Callas pdfToolbox in not installed.';
            }

            if (!$cliStatus) {
                $cliStatus = 'Callas pdfToolbox in not installed.';
            }

        } catch (\Throwable $exception) {
            $cliVersion = 'Callas pdfToolbox in not installed.';
            $cliStatus = 'Callas pdfToolbox in not installed.';
        }

        $this->set('cliVersion', $cliVersion);
        $this->set('cliStatus', $cliStatus);
    }

    /**
     * Trial method
     *
     * @return \Cake\Http\Response|null
     */
    public function requestTrial()
    {
        $callas = new CallasCommands();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $registration_name = $this->request->getData('trial_registration_name');
            $company_name = $this->request->getData('trial_company_name');
            $activationInformation = $callas->trialCallas($registration_name, $company_name, 'array');

            if ($activationInformation['return'] == 0) {
                $activationInformation = implode("\r\n", $activationInformation['message']);
            } else {
                $activationInformation = "Error Generating Trial Request Information!";
            }

            $registrationName = $registration_name;
            $companyName = $company_name;

        } else {
            $activationInformation = null;
            $registrationName = null;
            $companyName = null;
        }

        $this->set('activationInformation', $activationInformation);
        $this->set('trialRegistrationName', $registrationName);
        $this->set('trialCompanyName', $companyName);
    }

    /**
     * Request Activation method
     *
     * @return \Cake\Http\Response|null
     */
    public function requestActivation()
    {
        $callas = new CallasCommands();

        if ($this->request->is(['patch', 'post', 'put'])) {

            /** @var UploadedFile $activationPdf */
            $activationPdf = $this->request->getData('license_pdf');

            if ($activationPdf->getError() == 0 && $activationPdf->getClientMediaType() == 'application/pdf') {
                $licensePdfSaved = TMP . "License.pdf";
                $pdfData = ($activationPdf->getStream()->getContents());
                file_put_contents($licensePdfSaved, $pdfData);
            } else {
                $this->Flash->error(__('Request activation requires a valid License PDF file!'));
                return $this->redirect(['action' => 'request-activation']);
            }

            $registration_name = $this->request->getData('registration_name');
            $company_name = $this->request->getData('company_name');
            $activationInformation = $callas->requestActivationCallas($registration_name, $company_name, $licensePdfSaved, 'array');


            if ($activationInformation['return'] == 0) {
                $activationInformation = implode("\r\n", $activationInformation['message']);
            } else {
                $activationInformation = "Error Generating Trial Request Information!";
            }

            $registrationName = $registration_name;
            $companyName = $company_name;
            $licenseKey = null;

        } else {
            $activationInformation = null;
            $registrationName = null;
            $companyName = null;
            $licenseKey = null;
        }

        $this->set('activationInformation', $activationInformation);
        $this->set('registrationName', $registrationName);
        $this->set('companyName', $companyName);
        $this->set('licenseKey', $licenseKey);
    }

    /**
     * Register method
     *
     * @return \Cake\Http\Response|null
     */
    public function activate()
    {
        $callas = new CallasCommands();

        if ($this->request->is(['patch', 'post', 'put'])) {

            /** @var UploadedFile $activationPdf */
            $activationPdf = $this->request->getData('activation_pdf');

            if ($activationPdf->getError() == 0 && $activationPdf->getClientMediaType() == 'application/pdf') {
                $activationPdfSaved = TMP . "Activation.pdf";
                $pdfData = ($activationPdf->getStream()->getContents());
                file_put_contents($activationPdfSaved, $pdfData);
                $activationResult = $callas->activateCallas($activationPdfSaved);
            } else {
                $this->Flash->error(__('Activation requires a valid Activation PDF file!'));
                return $this->redirect(['action' => 'activate']);
            }

            if ($activationResult['status'] != 'OK') {
                $this->Flash->error(__('Activation of Callas pdfToolbox failed with the supplied PDF.'));
                $this->Flash->error(json_encode($activationResult, JSON_PRETTY_PRINT));
                return $this->redirect(['action' => 'activate']);
            }

            if ($activationResult['status'] == 'OK') {
                $this->Flash->success(__('Activation of Callas pdfToolbox succeeded!'));
                return $this->redirect(['action' => 'index']);
            }

        }

    }

    /**
     * Test method
     *
     * @return \Cake\Http\Response|null
     */
    public function test()
    {
        $success = true;

        if ($success) {
            $this->Flash->success(__('Callas pdfToolbox test succeeded.'));
        } else {
            $this->Flash->error(__('Callas pdfToolbox test failed. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}

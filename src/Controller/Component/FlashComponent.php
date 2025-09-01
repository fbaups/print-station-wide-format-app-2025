<?php

namespace App\Controller\Component;

use Cake\Controller\Component\FlashComponent as CakeFlashComponent;

/**
 * Extension to the Cake Flash component
 * Mainly used to avoid IDE Errors by way of DocBlocks
 *
 * @method void danger(string $message, array $options = []) Set a message using "danger" element
 * @method void dangerHtml(string $message, array $options = []) Set a message using "danger" element
 * @method void default(string $message, array $options = []) Set a message using "default" element
 * @method void defaultHtml(string $message, array $options = []) Set a message using "default" element
 * @method void error(string $message, array $options = []) Set a message using "error" element
 * @method void errorHtml(string $message, array $options = []) Set a message using "error" element
 * @method void info(string $message, array $options = []) Set a message using "info" element
 * @method void infoHtml(string $message, array $options = []) Set a message using "info" element
 * @method void success(string $message, array $options = []) Set a message using "success" element
 * @method void successHtml(string $message, array $options = []) Set a message using "success" element
 * @method void warning(string $message, array $options = []) Set a message using "warning" element
 * @method void warningHtml(string $message, array $options = []) Set a message using "warning" element
 */
class FlashComponent extends CakeFlashComponent
{

    /**
     * Set multiple Flash Messages
     *
     * $messages is a numerically indexed multi-dimensional array with the subkeys that follow the FlashComponent
     *
     * - `message` The message displayed in the GUI
     * - `key` The key to set under the session's Flash key
     * - `element` The element used to render the flash message. Default to 'default'.
     * - `params` An array of variables to make available when using an element
     * - `clear` A bool stating if the current stack should be cleared to start a new one
     * - `escape` Set to false to allow templates to print out HTML content
     *
     * @param array $messages
     */
    public function setMultiple(array $messages = []): void
    {
        foreach ($messages as $message) {

            $options = $message + $this->getConfig();
            unset($options['message']);

            $this->set($message['message'], $options);
        }
    }


    /**
     * Try to call the right flash type based on the error string
     *
     * @param string $message
     * @param array $options
     */
    public function smart(string $message, array $options = []): void
    {
        if (str_contains(strtolower($message), 'success')) {
            $this->success($message, $options);
        } elseif (str_contains(strtolower($message), 'error')) {
            $this->error($message, $options);
        } elseif (str_contains(strtolower($message), 'warning')) {
            $this->warning(__($message, $options));
        } elseif (str_contains(strtolower($message), 'danger')) {
            $this->danger($message, $options);
        } else {
            $this->info(__($message, $options));
        }
    }


    /**
     * Flashes the Alerts as presented by ReturnAlerts::getAllAlertsForMassInsert()
     *
     * @param $massInsert
     * @param array $options
     */
    public function flashMassInsertAlerts($massInsert, array $options = []): void
    {

        foreach ($massInsert as $log) {
            if (!is_string($log['message'])) {
                $message = json_encode($log['message']);
            } else {
                $message = $log['message'];
            }

            if ($message[0] === '"') {
                if (strrev($message)[0] === '"') {
                    $message = substr($message, 1, strlen($message) - 2);
                }
            }

            if ($log['level'] === 'success') {
                $this->success($message, $options);
            } elseif ($log['level'] === 'error') {
                $this->error($message, $options);
            } elseif ($log['level'] === 'warning') {
                $this->warning($message, $options);
            } elseif ($log['level'] === 'danger') {
                $this->warning($message, $options);
            } else {
                $this->info($message, $options);
            }
        }
    }

    /**
     * Flashes the Alerts as presented by ReturnAlerts::getAllAlerts()
     *
     * @param $allAlerts
     * @param array $options
     */
    public function flashAllAlerts($allAlerts, array $options = []): void
    {
        $types = [
            'success',
            'danger',
            'warning',
            'info',
        ];

        foreach ($types as $type) {
            $alerts = $allAlerts[$type];
            foreach ($alerts as $alert) {
                if ($type === 'success') {
                    $this->success($alert, $options);
                } elseif ($type === 'error') {
                    $this->error($alert, $options);
                } elseif ($type === 'warning') {
                    $this->warning($alert, $options);
                } elseif ($type === 'danger') {
                    $this->warning($alert, $options);
                } elseif ($type === 'info') {
                    $this->info($alert, $options);
                }
            }
        }
    }

}

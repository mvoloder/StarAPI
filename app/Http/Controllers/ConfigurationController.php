<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Symfony\Component\Debug\Exception\FatalErrorException;

class ConfigurationController extends Controller
{
    /**
     * Get Configuration from sharedSettings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfiguration($email = false)
    {
        $internalSettings = \Config::get('sharedSettings.internalConfiguration', []);
        $externalSettings = \Config::get('sharedSettings.externalConfiguration', []);
        $allsettings = [];
        $allsettings['internal'] = $internalSettings;

        if (empty($internalSettings) && empty($externalSettings)) {
            return $this->jsonError(['Empty settings list.'], 404);
        }
        
        foreach ($externalSettings as $name => $configs) {
            foreach ($configs as $config) {
                if (!key_exists('resolver', $config)) {
                    continue;
                }
                try {
                    $value = call_user_func([$config['resolver']['class'], $config['resolver']['method']]);
                } catch (\Exception $e) {
                    continue;
                }
                $allsettings[$name][$config['settingName']] = $value;
            }
        }

        if ($email !== false) {
            $response = [];
            $response['teamName']   = $allsettings['slack']['teamInfo']->team->name;
            $response['teamDomain'] = $allsettings['slack']['teamInfo']->team->domain;
            $response['teamDomain'] = $allsettings['slack']['teamInfo']->team->domain;

            return $response;
        }

        return $this->jsonSuccess($allsettings);
    }
}

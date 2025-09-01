<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;
use Cake\Routing\Router;

class SeedSettingsArtifactsRepo2 extends BaseMigration
{

    public function up()
    {
        $this->seedSettings();
    }

    public function down()
    {
    }

    public function seedSettings()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $defaultDomain = Router::fullBaseUrl();

        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Default Domain',
                'description' => 'The default domain to use when one cannot be determined (e.g. in CLI mode).',
                'property_group' => 'install',
                'property_key' => 'default_domain',
                'property_value' => $defaultDomain,
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => false
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Trust Proxy Header',
                'description' => 'If the Application is installed behind a proxy or load balancer, should the `X-Forwarded-Proto` header be trusted?',
                'property_group' => 'install',
                'property_key' => 'trust_proxy_header',
                'property_value' => 'false',
                'selections' => '{"false":"False","true":"True"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => false
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}

<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunnerDrupal\TaskRunner\Commands;

/**
 * Class Drupal7Commands.
 */
class Drupal7Commands extends DrupalCommands
{
    /**
     * @param string $settings_override_filename
     *
     * @return string
     */
    protected function getSettingsSetupAddendum($settings_override_filename)
    {
        return <<< EOF
/**
 * Include Drupal 7 settings overrides.
 *
 * The following file is generated by the openeuropa/task-runner project
 * using configuration from your local "runner.yml.dist/runner.yml" files.
 *
 * Keep this code block at the end of the file.
 */
\$conf_path = conf_path();
if (file_exists(DRUPAL_ROOT . '/' . \$conf_path . '/$settings_override_filename')) {
  include DRUPAL_ROOT . '/' . \$conf_path . '/$settings_override_filename';
}
EOF;
    }
}

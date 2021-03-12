<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunnerDrupal\Tests;

use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests various commands.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommandsTest extends AbstractTest
{
    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drushSetupDataProvider
     */
    public function testDrushSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("drupal:drush-setup --working-dir=" . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drupal7SettingsSetupDataProvider
     */
    public function testDrupal7SettingsSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');

        $input = new StringInput('drupal:setup-settings --working-dir=' . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = $config['drupal']['site']['settings_override_file'] ??
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drupal8SettingsSetupDataProvider
     */
    public function testDrupal8SettingsSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');

        $input = new StringInput('drupal:setup-settings --working-dir=' . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = $config['drupal']['site']['settings_override_file'] ??
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider settingsSetupForceDataProvider
     */
    public function testSettingsSetupForce(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        $filename = $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php';
        file_put_contents($filename, '');
        $filename = $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/settings.php';
        file_put_contents($filename, '# Already existing file.');

        $input = new StringInput('drupal:setup-settings --working-dir=' . $this->getSandboxRoot());

        if (true === $config['drupal']['site']['force']) {
            $input = new StringInput('drupal:setup-settings --working-dir=' . $this->getSandboxRoot() . ' --force');
        }
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = $config['drupal']['site']['settings_override_file'] ??
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
    }

    /**
     * Tests that existing commands can be overridden in the runner config.
     *
     * @dataProvider overrideCommandDataProvider
     *
     * @param string $command
     *   A command that will be executed by the task runner.
     * @param array $runnerConfig
     *   An array of task runner configuration data, equivalent to what would be
     *   written in a "runner.yml" file. This contains the overridden commands.
     * @param array $expected
     *   An array of strings which are expected to be output to the terminal
     *   during execution of the command.
     */
    public function testOverrideCommand($command, array $runnerConfig, array $expected)
    {
        $runnerConfigFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($runnerConfigFile, Yaml::dump($runnerConfig));

        $input = new StringInput("{$command} --working-dir=" . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $exit_code = $runner->run();

        // Check that the command succeeded, i.e. has exit code 0.
        $this->assertEquals(0, $exit_code);

        // Check that the output is as expected.
        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
        }
    }

    /**
     * @return array
     */
    public function drushSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-drush-setup.yml');
    }

    /**
     * @return array
     */
    public function drupal7SettingsSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal7-settings-setup.yml');
    }

    /**
     * @return array
     */
    public function drupal8SettingsSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal8-settings-setup.yml');
    }

    /**
     * @return array
     */
    public function settingsSetupForceDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-settings-setup-force.yml');
    }

    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return $this->getFixtureContent('setup.yml');
    }

    /**
     * Provides test cases for ::testOverrideCommand().
     *
     * @return array
     *   An array of test cases, each one an array with the following keys:
     *   - 'command': A string representing a command that will be executed by
     *     the task runner.
     *   - 'runnerConfig': An array of task runner configuration data,
     *     equivalent to what would be written in a "runner.yml" file.
     *   - 'expected': An array of strings which are expected to be output to
     *     the terminal during execution of the command.
     *
     * @see \OpenEuropa\TaskRunner\Tests\Commands\CommandsTest::testOverrideCommand()
     */
    public function overrideCommandDataProvider(): array
    {
        return $this->getFixtureContent('override.yml');
    }

    /**
     * @param string $content
     * @param array  $expected
     */
    protected function assertContainsNotContains($content, array $expected)
    {
        $this->assertContains($expected['contains'], $content);
        if (!empty($expected['not_contains'])) {
            $this->assertNotContains($expected['not_contains'], $content);
        }
    }
}

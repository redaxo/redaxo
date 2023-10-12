<?php

class rex_package_conflicts
{
    private $packages = [];

    public function getComposerConflicts(): ?string {
        $this->addCoreComposerJson();
        $this->addAddonsComposerJson();

        return $this->runComposer();
    }

    private function addCoreComposerJson(): void {
        $composerJson = rex_path::core('composer.json');
        $this->addRequirements($composerJson);
    }

    private function addAddonsComposerJson(): void {
        $addons = rex_addon::getRegisteredAddons();

        foreach($addons as $a) {
            if(!$a->isAvailable()) {
                continue;
            }

            $composerJsonPath = $a->getPath('composer.json');
            $this->addRequirements($composerJsonPath);
        }
    }

    private function addRequirements($composerJsonPath): void {

        if(!is_file($composerJsonPath)) {
            return;
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);

        if (!array_key_exists('require', $composerJson) || !array_key_exists('name', $composerJson)) {
            return;
        }

        $packageName  = $composerJson['name'];

        $this->packages[$packageName] = dirname($composerJsonPath);
    }

    private function runComposer(): ?string
    {
        $path = rex_path::cache('composer/composer.json');
        rex_dir::create(dirname($path));

        $composerJson = [
            'minimum-stability' => 'dev',
        ];
        foreach ($this->packages as $package => $packagePath) {
            $composerJson['require'][$package] = '*';
            $composerJson['repositories'][] = [
                'type' => 'path',
                'url' => $packagePath,
            ];
        }

        file_put_contents($path, json_encode($composerJson, JSON_PRETTY_PRINT));
        $command =  'composer update --dry-run --no-interaction --no-progress --no-scripts --no-plugins --no-autoloader --no-install --working-dir=' . rex_path::cache('composer'). ' 2>&1';

        exec($command, $output, $return_var);

        unlink($path);

        if($return_var !== 0) {
            $composerLog = implode("\n", $output);

            // only show the real problems
            return substr($composerLog, strpos($composerLog, PHP_EOL.PHP_EOL)+2);
        }

        return null;
    }
}

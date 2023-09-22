<?php

class rex_package_conflicts
{
    private $requirements = [];

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

            $composerJson = $a->getPath('composer.json');
            $this->addRequirements($composerJson);
        }
    }

    private function addRequirements($composerJson): void {

        if(!is_file($composerJson)) {
            return;
        }

        $composerJson = json_decode(file_get_contents($composerJson), true);

        if(!isset($composerJson['require'])) {
            return;
        }

        $require = $composerJson['require'];

        foreach($require as $package => $versionContraint) {
            if (isset($this->requirements[$package])) {
                $this->requirements[$package][] = $versionContraint;
            } else {
                $this->requirements[$package] = [$versionContraint];
            }
        }
    }

    private function runComposer(): ?string
    {
        $path = rex_path::cache('composer/composer.json');
        rex_dir::create(dirname($path));

        $combinedRequirements = [];
        foreach ($this->requirements as $package => $versionContraints) {
            $combinedRequirements[$package] = implode(',', $versionContraints);
        }

        file_put_contents($path, json_encode(['require' => $combinedRequirements], JSON_PRETTY_PRINT));
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

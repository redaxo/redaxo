<?php

class rex_command_config_get_test extends \PHPUnit\Framework\TestCase {

    public function testGetConfig() {
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(new rex_command_config_get());
        $website = $commandTester->execute([
            'website'
        ]);
        $this->assertEquals('website', $website);
    }
}

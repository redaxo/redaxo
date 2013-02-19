<?php

class rex_var_test_var extends rex_var
{
    public function getOutput()
    {
        return $this->getParsedArg('content', "'default'", true);
    }

    public static function quote($string)
    {
        // make quote() public
        return parent::quote($string);
    }
}

class rex_var_test extends rex_var_base_test
{
    public function parseTokensProvider()
    {
        return array(
            array('aREX_TEST_VAR[content=b]c', 'abc'),
            array('a<?php echo \'bREX_TEST_VAR[content=c]d\'; ?>e', 'abcde'),
            array('a<?php echo "bREX_TEST_VAR[content=c]d"; ?>e', 'abcde'),
            array('a<?php echo REX_TEST_VAR[content=b]; ?>c', 'abc'),
            array('a<?php echo <<<EOT
bREX_TEST_VAR[content=c]d
EOT;
?>e', 'abcde'),
            array('a<?php echo <<<\'EOT\'
bREX_TEST_VAR[content=c]d
EOT;
?>e', 'abcde')
        );
    }

    /**
     * @dataProvider parseTokensProvider
     */
    public function testParseTokens($content, $expectedOutput)
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    public function parseArgsSyntaxProvider()
    {
        return array(
            array('REX_TEST_VAR[]', 'default'),
            array('REX_TEST_VAR[""]', ''),
            array('REX_TEST_VAR[ab]', 'ab'),
            array('REX_TEST_VAR["ab c"]', 'ab c'),
            array('REX_TEST_VAR[REX_TEST_VAR[ab]]', 'ab'),
            array('REX_TEST_VAR[content=ab]', 'ab'),

            array(<<<'EOT'
REX_TEST_VAR[content="a 'b' \"c\" \ \\ \\\[d\]"]
EOT
                , 'a \'b\' "c" \ \ \[d]'),

            array(<<<'EOT'
REX_TEST_VAR[content="a REX_TEST_VAR[content=\"b 'c' \\\"d\\\" \ \\ \\\[e\]\"] f"]
EOT
                , 'a b \'c\' "d" \ \ \[e] f'),

            array(<<<'EOT'
REX_TEST_VAR[content="REX_TEST_VAR[content='\'a\' \"b\"']"]
EOT
                , '\'a\' "b"'),

            array(<<<'EOT'
<?php echo "REX_TEST_VAR[content=\"a 'b' \\\"c\\\" \ \\ \\\[d\]\"]";
EOT
                , 'a \'b\' "c" \ \ \[d]'),
            array(<<<'EOT'
<?php echo 'REX_TEST_VAR[content="a \'b\' \"c\" \ \\ \\\[d\]"]';
EOT
            , 'a \'b\' "c" \ \ \[d]'),

            array(<<<'EOT'
<?php echo 'REX_TEST_VAR[content=\'REX_TEST_VAR[content="a \\\'b\\\' \"c\" \ \\ \\\[d\]"]\']';
EOT
                , 'a \'b\' "c" \ \ \[d]'),

            array(<<<'EOT'
REX_TEST_VAR[
    content="ab
cd ef"
]
EOT
                , "ab\ncd ef"),
            array('REX_NON_EXISTING[REX_TEST_VAR[ab]]', 'REX_NON_EXISTING[ab]'),
            array('REX_TEST_VAR[REX_NON_EXISTING[]]', 'REX_NON_EXISTING[]')
        );
    }

    /**
     * @dataProvider parseArgsSyntaxProvider
     */
    public function testParseArgsSyntax($content, $expectedOutput)
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    public function parseGlobalArgsProvider()
    {
        return array(
            array('REX_TEST_VAR[content=ab instead=cd]', 'cd'),
            array('REX_TEST_VAR[content="" instead=cd]', ''),
            array('REX_TEST_VAR[content=ab ifempty=cd]', 'ab'),
            array('REX_TEST_VAR[content="" ifempty=cd]', 'cd'),
            array('REX_TEST_VAR[content="" ifempty="REX_TEST_VAR[cd]"]', 'cd'),
            array('REX_TEST_VAR[content=ab instead=cd ifempty=ef]', 'cd'),
            array('REX_TEST_VAR[content="" instead=cd ifempty=ef]', 'ef'),
            array('REX_TEST_VAR[content=cd prefix=ab]', 'abcd'),
            array('REX_TEST_VAR[content="" prefix=ab]', ''),
            array('REX_TEST_VAR[content=cd suffix=ef]', 'cdef'),
            array('REX_TEST_VAR[content="" suffix=ef]', ''),
            array('REX_TEST_VAR[content=cd prefix=ab suffix=ef]', 'abcdef'),
            array('REX_TEST_VAR[content="" prefix=ab suffix=ef]', ''),
            array('REX_TEST_VAR[content=cd prefix=ab suffix=ef instead=gh ifempty=ij]', 'abghef'),
            array('REX_TEST_VAR[content="" prefix=ab suffix=ef instead=gh ifempty=ij]', 'abijef'),
            array('REX_TEST_VAR[content=ab callback="rex_var_test::callback" suffix=cd]', 'subject:ab content:ab suffix:cd'),
            array('REX_TEST_VAR[content="REX_TEST_VAR[ab]" callback="rex_var_test::callback" suffix=cd]', 'subject:ab content:ab suffix:cd')
        );
    }

    public static function callback($params)
    {
        return sprintf('subject:%s content:%s suffix:%s', $params['subject'], $params['content'], $params['suffix']);
    }

    /**
     * @dataProvider parseGlobalArgsProvider
     */
    public function testParseGlobalArgs($content, $expectedOutput)
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    public function testToArray()
    {
        $content = '<?php echo rex_var::toArray("REX_TEST_VAR[content=\'test\']") === null ? "null" : "";';
        $this->assertParseOutputEquals('null', $content, 'toArray() returns null for non-arrays');

        $array = array('1', '3', 'test');

        $content = '<?php print_r(rex_var::toArray("REX_TEST_VAR[content=\'' . addcslashes(json_encode($array), '[]"')  . '\']"));';
        $this->assertParseOutputEquals(print_r($array, true), $content, 'toArray() works with non-htmlspecialchar\'ed data');

        $content = '<?php print_r(rex_var::toArray("REX_TEST_VAR[content=\'' . addcslashes(htmlspecialchars(json_encode($array)), '[]"')  . '\']"));';
        $this->assertParseOutputEquals(print_r($array, true), $content, 'toArray() works with htmlspecialchar\'ed data');
    }

    public function testQuote()
    {
        $string = "abc 'de' \"fg\" \ \nh\r\ni";
        $expected = <<<'EOD'
'abc \'de\' "fg" \\ ' . "\n" . 'h' . "\r\n" . 'i'
EOD;

        $this->assertEquals($expected, rex_var_test_var::quote($string));
    }
}

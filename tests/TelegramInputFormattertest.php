<?php
/**
 * Created by PhpStorm.
 * User: pulung
 * Date: 26/12/17
 * Time: 19.31
 */
use App\Helpers\CommandFinder;
class CommandFinderTest extends TestCase
{
    public $finder;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->finder = new CommandFinder();
    }
    public function testBotUsername() {
        $this->assertNotEquals("", env("BOT_USERNAME"));
    }
    public function testFindingCommand() {
        $result = $this->finder->findCommand("/start", true);
        $this->assertEquals("start", $result);
        $result = $this->finder->findCommand("/start@". env("BOT_USERNAME"), false);
        $this->assertEquals("start", $result);
        $result = $this->finder->findCommand("/start", false);
        $this->assertEquals("", $result);
    }
    public function testFindingYearParams() {
        $result = $this->finder->findYearParams("/start 2099");
        $this->assertEquals("2099", $result);
        $result = $this->finder->findYearParams("/start@". env("BOT_USERNAME") ." 2099");
        $this->assertEquals("2099", $result);
    }
}
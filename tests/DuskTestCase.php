<?php

namespace Tests;

use Derekmd\Dusk\Concerns\TogglesHeadlessMode;
use Derekmd\Dusk\Firefox\SupportsFirefox;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    use SupportsFirefox, TogglesHeadlessMode;

    /**
     * Prepare for Dusk test execution.
     *
     * @return void
     */
    #[BeforeClass]
    public static function prepare()
    {
        if (static::runningFirefoxInSail()) {
            return;
        }

        if (env('DUSK_CHROME')) {
            static::startChromeDriver(['--port=9515']);
        } else {
            static::startFirefoxDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        if (env('DUSK_CHROME')) {
            return $this->chromeDriver();
        }

        return $this->firefoxDriver();
    }

    /**
     * Create the ChromeDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function chromeDriver()
    {
        $options = (new ChromeOptions)->addArguments($this->filterHeadlessArguments([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ]));

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Create the Geckodriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function firefoxDriver()
    {
        $capabilities = DesiredCapabilities::firefox();

        $capabilities->getCapability(FirefoxOptions::CAPABILITY)
            ->addArguments($this->filterHeadlessArguments([
                '--window-size=1920,1080',
            ]))
            ->setPreference('devtools.console.stdout.content', true);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:4444',
            $capabilities
        );
    }
}

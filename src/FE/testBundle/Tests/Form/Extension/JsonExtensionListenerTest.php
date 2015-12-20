<?php

namespace FE\testBundle\Tests\Form\Extension;

use FE\testBundle\Form\Extension\JsonExtensionListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class JsonExtensionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonExtensionListener
     */
    private $jsonExtensionListener;

    /**
     * @var FormEvent
     */
    private $formEvent;

    public function setUp()
    {
        $this->jsonExtensionListener = new JsonExtensionListener();
        $this->formEvent = \Phake::mock('Symfony\Component\Form\FormEvent');
    }

    public function testShouldSubscribedPreSubmitEvents()
    {
        $this->assertEquals(
            array(
                FormEvents::PRE_SUBMIT => 'onPreSubmit',
            ),
            JsonExtensionListener::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testValidJsonShouldBeDecoded($json, $data)
    {
        \Phake::when($this->formEvent)
            ->getData()
            ->thenReturn($json);

        $this->jsonExtensionListener->onPreSubmit($this->formEvent);

        \Phake::verify($this->formEvent, \Phake::times(1))->setData($data);
    }

    public function testInvalidJsonShouldThrowHttpExceptionError()
    {
        $this->setExpectedExceptionRegExp(
          'Symfony\Component\HttpKernel\Exception\HttpException',
          '/^Invalid submitted json data, error (.*) : (.*), json : aze$/'
        );
        $json = 'aze';

        \Phake::when($this->formEvent)
            ->getData()
            ->thenReturn($json);

        $this->jsonExtensionListener->onPreSubmit($this->formEvent);
    }

    public function jsonProvider()
    {
        return array(
            array(
                '{ "name": "test" }',
                array('name' => 'test'),
            ),
            array(
                '{ "name": "Robert", "lastname": "Michel", "parent": { "name": "Michel", "lastname": "Robert" } }',
                array(
                    'name' => 'Robert',
                    'lastname' => 'Michel',
                    'parent' => array(
                        'name' => 'Michel',
                        'lastname' => 'Robert',
                    ),
                ),
            ),
        );
    }
}
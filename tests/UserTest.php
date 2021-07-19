<?php


namespace App\Tests;


class UserTest extends AbstractTest
{
    public function testRegisterSuccessfull(): void
    {
        $client = AbstractTest::getClient();
        //$client->followRedirect();
        $crawler = $client->request('GET', '/registration');

        //$buttonCrawlerNode = $crawler->selectButton('Вы должны согласиться с правилами');
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "registration[email]" => "tema@tema.com",
            "registration[password][first]"  => '123456',
            "registration[password][second]" => "123456",
            "registration[agreeTerms]" => true,
        ));

        $client->submit($form);

        $this->assertResponseRedirect();

        $crawler = $client->followRedirect();
        $this->assertSame('http://localhost/course/', $crawler->getUri());
    }

    public function testAuthorization(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, 'user@user.com', 'pass_123456');
        $this->assertResponseRedirect();

        $crawler = $client->followRedirect();
        $this->assertSame('http://localhost/course/', $crawler->getUri());

    }
}
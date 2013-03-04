<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\PSC,
    
    Psc\Doctrine\Helper as DoctrineHelper
  ;

class CreateUserCommand extends Command {

  protected function configure() {
    $this
      ->setName('cms:create-user')
      ->setDescription(
        'Erstellt einen neuen User Zugang'
      )
      ->setDefinition(array(
        new InputArgument(
          'email',InputArgument::REQUIRED,'die E-Mail-Adresse des Users'
        ),
        new InputOption(
          'con', '', InputOption::VALUE_OPTIONAL,
          'Name der Connection',
          'default'
        ),
        new InputOption(
          'reset-password','r',InputOption::VALUE_NONE,
          'updated den bestehnden User und erzeugt ein neues Passwort'
        ),
        new InputOption(
          'password','p',InputOption::VALUE_REQUIRED,
          'wird kein passwort gesetzt wird eins automatisch generiert (6 Zeichen) und ausgegeben'
        ),
      ));
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $project = \Psc\PSC::getProject();
    $em = $project->getModule('Doctrine')->getEntityManager($input->getOption('con'));
    
    try {
      $r = new \Psc\Form\EmailValidatorRule();
      $email = $r->validate($input->getArgument('email'));
    } catch (\Exception $e) {
      $output->writeln('FEHLER: E-Mail-Adresse ist nicht korrekt.');
      return 1;
    }
    
    if (($password = $input->getOption('password')) == NULL) {
      $password = \Webforge\Common\String::random(6);
    }
    
    $c = $project->getUserClass();
    if ($input->getOption('reset-password') == TRUE) {
      $userRep = $em->getRepository($c);
      $user = $userRep->find($email); 
      
      if (!($user instanceof \Psc\CMS\User)) {
        $output->writeln('User mit der Email: "'.$email.'" nicht gefunden.');
        return 2;
      }
      
      $output->writeln('Passwort Reset für: '.$email);
    } else {
      $user = new $c($email);
    }
    
    $user->hashPassword($password);
    try {
      $em->persist($user);
      $em->flush();
    } catch (\PDOException $e) {
      if (\Psc\Doctrine\Exception::convertPDOException($e) instanceof \Psc\Doctrine\UniqueConstraintException) {
        $output->writeln('User existiert bereits. --reset-password / -r nehmen, um ein neues Passwort zu erzeugen');
        return 3;
      }
      
      throw $e;
    }
    
    $output->writeln(sprintf('Passwort: "%s"',$password));
    $output->writeln('done.');

    return 0;
  }
}
?>
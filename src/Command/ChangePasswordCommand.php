<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ChangePasswordCommand extends UserCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:change-password';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Change the password of a user.')
			 ->addArgument('password', InputArgument::REQUIRED, 'The password')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:change-password</info> command changes the password of a user:

  <info>php %command.full_name% admin</info>

This interactive shell will first ask you for a password.

You can alternatively specify the password as a second argument:

  <info>php %command.full_name% admin mypassword</info>

EOF
			 );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		parent::interact($input, $output);
		
		if (!$input->getArgument('password')) {
			$question = new Question('Please enter the new password:');
			$question->setValidator(function($password){
				if (true === empty($password)) {
					throw new \Exception('Password can not be empty');
				}
				
				return $password;
			});
			$question->setHidden(true);
			
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument('password', $answer);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$username = $input->getArgument('username');
		$this->manipulator->changePassword($username, $input->getArgument('password'));
		
		$output->writeln(sprintf('Changed password for user <comment>%s</comment>', $username));
	}
}

<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class CreateUserCommand extends UserCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:create';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Create a user.')
			 ->addArgument('password', InputArgument::REQUIRED, 'The password')
			 ->addOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:create</info> command creates a user:

  <info>php %command.full_name% admin</info>

This interactive shell will ask you for a password.

You can alternatively specify the password as the second arguments:

  <info>php %command.full_name% admin mypassword</info>

You can create an inactive user (will not be able to log in):

  <info>php %command.full_name% admin --inactive</info>
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
			$question = new Question('Please choose a password:');
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
		$this->userManipulator->create($username, $input->getArgument('password'), !$input->getOption('inactive'));
		
		$output->writeln(sprintf('Created user <comment>%s</comment>', $username));
	}
}

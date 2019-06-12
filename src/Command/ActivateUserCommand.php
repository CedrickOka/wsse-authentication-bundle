<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ActivateUserCommand extends UserCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:activate';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Activate a user')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:activate</info> command activates a user (so they will be able to log in):

  <info>php %command.full_name% admin</info>
EOF
			);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$username = $input->getArgument('username');
		$this->userManipulator->activate($username);
		
		$output->writeln(sprintf('User "%s" has been activated.', $username));
	}
}

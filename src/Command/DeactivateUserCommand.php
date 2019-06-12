<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class DeactivateUserCommand extends UserCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:deactivate';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Deactivate a user')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:deactivate</info> command deactivates a user (will not be able to log in)

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
		$this->userManipulator->deactivate($username);
		
		$output->writeln(sprintf('User "%s" has been deactivated.', $username));
	}
}

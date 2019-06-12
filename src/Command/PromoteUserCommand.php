<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PromoteUserCommand extends RoleCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:promote';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Promotes a user by adding a role')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:promote</info> command promotes a user by adding a role

  <info>php %command.full_name% admin ROLE_CUSTOM</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeRoleCommand($username, $role, OutputInterface $output)
	{
		if ($this->manipulator->addRole($username, $role)) {
			$output->writeln(sprintf('Role "%s" has been added to user "%s".', $role, $username));
		} else {
			$output->writeln(sprintf('User "%s" did already have "%s" role.', $username, $role));
		}
	}
}

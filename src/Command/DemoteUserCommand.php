<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class DemoteUserCommand extends RoleCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:promote';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Demote a user by removing a role')
			 ->setHelp(<<<EOF
The <info>oka:wsse-authentication:user:promote</info> command demotes a user by removing a role

  <info>php %command.full_name% admin ROLE_CUSTOM</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeRoleCommand($username, $role, OutputInterface $output)
	{
		if ($this->manipulator->removeRole($username, $role)) {
			$output->writeln(sprintf('Role "%s" has been removed from user "%s".', $role, $username));
		} else {
			$output->writeln(sprintf('User "%s" didn\'t have "%s" role.', $username, $role));
		}
	}
}

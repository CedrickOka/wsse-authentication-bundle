<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class DisallowIpUserCommand extends AllowedIpCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:ip-disallow';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Demotes a user by removing an IP')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-disallow-ip</info> command demotes a user by removing an IP

  <info>php %command.full_name% admin 127.0.0.1</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeAllowedIpCommand($username, $ip, OutputInterface $output)
	{
		if ($this->manipulator->removeAllowedIp($username, $ip)) {
			$output->writeln(sprintf('IP "%s" has been removed to user "%s".', $ip, $username));
		} else {
			$output->writeln(sprintf('User "%s" didn\'t have "%s" IP.', $username, $ip));
		}
	}
}

<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class AllowIpUserCommand extends AllowedIpCommand
{
	protected static $defaultName = 'oka:wsse-authentication:user:ip-allow';
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName(static::$defaultName)
			 ->setDescription('Promotes a user by adding an IP')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-allow-ip</info> command promotes a user by adding an IP

  <info>php %command.full_name% admin 127.0.0.1</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeAllowedIpCommand($username, $ip, OutputInterface $output)
	{
		if ($this->manipulator->addAllowedIp($username, $ip)) {
			$output->writeln(sprintf('IP "%s" has been added to user "%s".', $ip, $username));
		} else {
			$output->writeln(sprintf('User "%s" did already have "%s" IP.', $username, $ip));
		}
	}
}

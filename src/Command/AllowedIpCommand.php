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
abstract class AllowedIpCommand extends UserCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->addArgument('ip', InputArgument::REQUIRED, 'The IP');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		parent::interact($input, $output);
		
		if (!$input->getArgument('ip')) {
			$question = new Question('Please choose a IP:');
			$question->setValidator(function($ip){
				if (true === empty($ip)) {
					throw new \Exception('IP can not be empty');
				}
				
				return $ip;
			});
			
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument('ip', $answer);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->executeAllowedIpCommand($input->getArgument('username'), $input->getArgument('ip'), $output);
	}

	/**
	 * @param string		  $username
	 * @param string		  $ip
	 * @param OutputInterface $output
	 */
	abstract protected function executeAllowedIpCommand($username, $ip, OutputInterface $output);
}

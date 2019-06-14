<?php
namespace Oka\WSSEAuthenticationBundle\Command;

use Oka\WSSEAuthenticationBundle\Util\WSSEUserManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class UserCommand extends Command
{
	/**
	 * @var WSSEUserManipulator $userManipulator
	 */
	protected $userManipulator;
	
	public function __construct(WSSEUserManipulator $userManipulator = null)
	{
		parent::__construct();
		
		$this->userManipulator = $userManipulator;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->addArgument('username', InputArgument::REQUIRED, 'The username');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		if (null === $this->userManipulator) {
			$output->writeln('<error>Install the bundles "doctrine/doctrine-bundle" or "doctrine/mongodb-odm-bundle" and configure "oka_wsse_authentication.user_class" for to be able to use this command.</error>');
			exit();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		if (!$input->getArgument('username')) {
			$question = new Question('Please choose a username:');
			$question->setValidator(function($username){
				if (true === empty($username)) {
					throw new \Exception('Username can not be empty');
				}
				
				return $username;
			});
			
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument('username', $answer);
		}
	}
}

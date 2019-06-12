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
abstract class RoleCommand extends UserCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->addArgument('role', InputArgument::REQUIRED, 'The role');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		parent::interact($input, $output);
		
		if (!$input->getArgument('role')) {
			$question = new Question('Please choose a role:');
			$question->setValidator(function($role){
				if (true === empty($role)) {
					throw new \Exception('Role can not be empty');
				}
				
				return $role;
			});
			
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument('role', $answer);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->executeRoleCommand($input->getArgument('username'), $input->getArgument('role'), $output);
	}
	
	/**
	 * @param string		  $username
	 * @param string		  $role
	 * @param OutputInterface $output
	 */
	abstract protected function executeRoleCommand($username, $role, OutputInterface $output);
}

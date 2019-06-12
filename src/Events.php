<?php
namespace Oka\WSSEAuthenticationBundle;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class Events
{
	/**
	 * The AUTHENTICATION_FAILURE event occurs when the user is created with WSSEUserManipulator.
	 *
	 * This event allows you to access the created user and to add some behaviour after the creation.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\AuthenticationFailureEvent")
	 */
	const AUTHENTICATION_FAILURE = 'oka_wsse_authentication.authentication_failure';
	
	/**
	 * The USER_CREATED event occurs when the user is created with WSSEUserManipulator.
	 *
	 * This event allows you to access the created user and to add some behaviour after the creation.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent")
	 */
	const USER_CREATED = 'oka_wsse_authentication.user.created';
	
	/**
	 * The USER_PASSWORD_CHANGED event occurs when the user is created with UserManipulator.
	 *
	 * This event allows you to access the created user and to add some behaviour after the password change.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent")
	 */
	const USER_PASSWORD_CHANGED = 'oka_wsse_authentication.user.password_changed';

	/**
	 * The USER_ACTIVATED event occurs when the user is created with WSSEUserManipulator.
	 *
	 * This event allows you to access the activated user and to add some behaviour after the activation.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent")
	 */
	const USER_ACTIVATED = 'oka_wsse_authentication.user.activated';

	/**
	 * The USER_DEACTIVATED event occurs when the user is created with WSSEUserManipulator.
	 *
	 * This event allows you to access the deactivated user and to add some behaviour after the deactivation.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent")
	 */
	const USER_DEACTIVATED = 'oka_wsse_authentication.user.deactivated';
	
	/**
	 * The USER_DELETED event occurs when the user is deleted with WSSEUserManipulator.
	 *
	 * This event allows you to access the deleted user and to add some behaviour after the creation.
	 *
	 * @Event("Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent")
	 */
	const USER_DELETED = 'oka_wsse_authentication.user.deleted';
}

<?php

return [

    'email_change' => [
        'action' => 'Confirm Email Change',
        'line1' => 'We received a request to change your email address.',
        'line2' => 'This link will expire in :minutes minutes.',
        'line3' => 'If you did not request this, no action is needed.',
        'subject' => 'Confirm Your Email Change',
    ],
    'password_changed' => [
        'line1' => 'Your account password was recently changed.',
        'line2' => 'If you did not make this change, please contact support immediately.',
        'subject' => 'Your Password Was Changed',
    ],
    'plan_cancelled' => [
        'line1' => 'Your plan has been cancelled and will remain active until the end of your billing period.',
        'subject' => 'Plan Cancelled',
    ],
    'plan_changed' => [
        'line1' => 'Your plan has been changed to :plan.',
        'line1_from' => 'Your plan has been changed from :from to :to.',
        'subject' => 'Plan Updated',
    ],
    'plan_resumed' => [
        'line1' => 'Your plan cancellation has been reversed. Your subscription will continue as normal.',
        'subject' => 'Plan Resumed',
    ],
    'plan_subscribed' => [
        'line1' => 'You have successfully subscribed to the :plan plan.',
        'subject' => 'Thanks for Subscribing!',
    ],
    'reset_password' => [
        'action' => 'Reset Password',
        'line1' => 'We received a request to reset your password.',
        'line2' => 'This link will expire in :minutes minutes.',
        'line3' => 'If you did not request this, no action is needed.',
        'subject' => 'Reset Your Password',
    ],
    'temp_password' => [
        'line1' => 'An administrator has reset your password.',
        'line2' => 'Your temporary password is: :password',
        'line3' => 'You will be required to change this password on your next login.',
        'subject' => 'Your Temporary Password',
    ],
    'verification' => [
        'line1' => 'Your verification code is:',
        'line2' => 'This code expires in :minutes minutes.',
        'subject' => 'Verify Your Email',
    ],
    'welcome' => [
        'line1' => 'Your account has been created successfully.',
        'line2' => 'You are all set to get started.',
        'subject' => 'Welcome!',
    ],

];

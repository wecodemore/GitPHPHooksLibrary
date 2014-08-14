<?php

# Fetch current commit state
$files  = array();
$return = 0;
exec( "git rev-parse --verify HEAD 2> /dev/null", $set, $return );

# Grab commited files
$against = $return === 0
	? 'HEAD'
	// or: diff against an empty tree object
	: '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

exec( "git diff-index --cached --full-index {$against}", $files );


echo "\n-------------------------\n";
echo " Running PHP Mess Detector\n";
echo "-------------------------\n";

$pattern = '/\.ph(tml|p)$/';
$exit_status = 0;
foreach ( $files as $file )
{
	$parts  = explode( " ", $file );
	$sha    = $parts[3];
	$name   = substr( $parts[4], 2 );
	$status = substr( $parts[4], 0, 1 );

	// don't check files that aren't PHP
	if ( ! preg_match( $pattern, $name ) )
		continue;

	// if the file has been moved or deleted,
	// the old filename should be skipped
	if ( ! file_exists( $name ) )
		continue;

	// If the file was deleted, skip it
	if ( "D" === $status )
		continue;

	$dir = getcwd();
	$output = array();
	$result = '';
	$cmd = sprintf(
		"{$dir}/vendor/bin/phpmd %s text {$dir}/config/.phpmd.xml",
		escapeshellarg( $name )
	);
	exec( $cmd, $output, $result );
	if ( $result > 0 )
	{
		$error = explode( "\t", $output[1] );
		echo " |- {$error[0]}\n";
		echo " `- {$error[1]}\n";
		$exit_status = 1;
	}
	else
	{
		echo " |- {$name}\n";
	}
}

if ( 0 === $exit_status )
{
	echo "-------------------------\n";
	echo " <3 All files mess free.\n";
	echo "-------------------------\n\n";
}
else
{
	echo "---------------------------------------\n";
	echo " Please fix the mess before commiting.\n";
	echo "---------------------------------------\n\n";
	# End and abort
	exit( $exit_status );
}

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
	$path   = str_replace( "{$status}\t", "", $parts[4] );

	// don't check files that aren't PHP
	if ( ! preg_match( $pattern, $name ) )
		continue;

	// if the file has been moved or deleted,
	// the old filename should be skipped
	if ( ! file_exists( $name ) )
		continue;

	// Unmerged
	if ( 'U' === $status )
	{
		echo " |- {$name} is unmerged. You must complete the merge before it can be committed.\n";
		continue;
	}

	// Internal Git Bug
	if ( 'X' === $status )
	{
		echo " |- {$name}: unknown status. Please file a bug report for git. Really.\n";
		continue;
	}
	// If the file was deleted, skip it
	if ( 'D' === $status )
		continue;

	$cwd = getcwd();
	$bin = "{$cwd}/vendor/bin/";
	if ( file_exists( "{$cwd}\\composer.json" ) )
	{
		$composer = json_decode( file_get_contents( "{$cwd}\\composer.json" ) );
		if (
			property_exists( $composer, 'config' )
			and isset( $composer->config->{"bin-dir"} )
		)
			$bin = str_replace( DIRECTORY_SEPARATOR, "/", "{$cwd}/{$composer->config->{"bin-dir"}}/" );
	}
	$output = array();
	$result = '';
	$cmd = sprintf(
		"{$bin}/phpmd %s text {$cwd}/config/.phpmd.xml",
		escapeshellarg( $name )
	);
	exec( $cmd, $output, $result );
	if (
		$result > 0
		and ! empty( $output )
		)
	{
		$error = explode( "\t", $output[1] );
		if ( count( $error ) >= 2 )
		{
			echo " |- {$error[0]}\n";
			echo " `- {$error[1]}\n";
		}
		elseif (
			count( $error ) === 1
			and strstr( $error[0], $path )
			)
		{
			echo " |- {$error[0]}\n";
		}
		else
		{
			echo " |- {$name}\n";
		}
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

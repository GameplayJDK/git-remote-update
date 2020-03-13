# git-remote-update

A tool for running `git remote update` on multiple directories.

## Installation

Run `composer create-project gameplayjdk/git-remote-update`. This will create a new project from this one and allow you
to change the `configuration.json` to your liking, e.g. enter your paths. When it suite your needs, try running the `app:git-remote-update` 
command to test if everything works.

## Usage

Create a cron-job to execute `php bin/console.php app:git-remote-update` on a regular basis.

That way all mirrored repositories configured will stay up to date.

## License

It's MIT.

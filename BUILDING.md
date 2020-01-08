# Building Righteous MIMEs!

To make things easy, **Righteous MIMEs!** comes with its very own containerized build environment loaded with all but _three_ dependencies. (You don't need to install a billion dangerous packages directly to your local machine! Hurray!)

In other words, all you need to build this library from scratch is:
* [Docker](https://www.docker.com/) (or [Podman](https://podman.io/) suitably aliased to `docker`).
* [Git](https://github.com/git/git) (but you surely have that already).
* [Just](https://github.com/casey/just).

In terms of system requirements, you'll need a decent Internet connection, about `1GB` of disk space for the expanded Docker image(s) (to be safe), and at least `256MB` of RAM to run the processes, though the more the merrier.

It should be noted if you just intend to _use_ this library, you don't need to _build_ it. Just follow the [installation instructions](README.md#installation) in the main README.


&nbsp;
## Getting Started

To get started, pop a terminal and run:

```bash
# Clone the repository.
git clone https://github.com/Blobfolio/righteous-mimes.git righteous-mimes

# Move to the source directory.
cd righteous-mimes/

# Run the Just Docker task.
just sandbox-launch
```

The first run will take a little time and a little bandwidth to sort out as the build container needs to be built, but after that things should go quickly.


&nbsp;
## Build Tasks

After running `just sandbox-launch` from your local machine, you'll be dropped into a Docker shell listing all of the available build tasks.

Run the one you want:

```bash
# Rebuild the dataset.
just data

# Watch PHP code for changes, linting and validating as you go.
just watch

# Run unit tests.
just test

# Show the list again.
just --list
```

When you're done, press `CTRL + D` (for most computers, anyway) to exit and return to your local machine.


&nbsp;
## Rinse and Repeat

To launch the build environment a second or third or hundredth time, pop back into the project directory and run `just sandbox-launch` again.

To update/rebuild the environment at any point in the future, run `just sandbox-rebuild`. Easy!

There is no automated removal process, but you can do that the normal Docker way, e.g.:

```bash
# Remove the build environment and its Debian base
# (if nothing else is using it).
docker rmi righteous/sandbox debian:buster-slim
```


&nbsp;
## Other Platforms

In addition to generating data for the **Righteous MIMEs!** PHP library, the `just data` build task also dumps data in `JSON` format to the project's `bin/` directory.

This platform-agnostic data can provide an excellent starting point for improved media type detection for e.g. Javascript, Rust, etc.

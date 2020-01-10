# Building Righteous MIMEs!

**Note:** if you just intend to _use_ this library, you don't need to _build_ it. Just follow the [installation instructions](README.md#installation) in the main README.

Even simple projects these days have massive build/development dependencies. Rather than requiring users try — and fail — to install millions of NPM packages, crates, and compilers locally — again and again for each damn project — we've decided to give containers a try.

This way, if you can run [Docker](https://www.docker.com/), you can build, develop, and/or test **Righteous MIMEs!**


&nbsp;
## Prerequisites:

Locally, you only need three applications installed:
* [Docker](https://www.docker.com/) (or [Podman](https://podman.io/) suitably aliased to `docker`).
* [Git](https://github.com/git/git) (but you surely have that already).
* [Just](https://github.com/casey/just).

The [Righteous Sandbox](https://github.com/Blobfolio/righteous-sandbox/) container is shared with several other Blobfolio projects so is a bit large — about 3.1GB — but only needs to be built once. Just make sure you have adequate disk space and a good Internet connection for the first run.


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

If you're developing — making changes to PHP, CSS, and/or JS — run `just watch`. That will watch the project directory for script changes and execute any linting, compressing, compiling, etc., sub-tasks any time there's a change.

To run the unit tests, run `just test`.

To manually recompile the MIME data, run `just data`.

When you're done, press `CTRL + D` (or whatever key combination you normally use to exit terminal sessions) to exit and return to your local machine.


&nbsp;
## Rinse and Repeat

To launch the build environment a second or third or hundredth time, pop back into the project directory and run `just sandbox-launch` again.

To update/rebuild the environment at any point in the future, run `just sandbox-rebuild`. Easy!

There is no automated removal process, but you can do that the normal Docker way, e.g.:

```bash
# Remove the build environment.
docker rmi righteous/sandbox
```


&nbsp;
## Other Platforms

In addition to generating data for the **Righteous MIMEs!** PHP library, the `just data` build task also dumps data in `JSON` format to the project's `bin/` directory.

This platform-agnostic data can provide an excellent starting point for improved media type detection for e.g. Javascript, Rust, etc.

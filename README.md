# Application Works

Web dashboard framework for creating applications and testing concepts.

## Private Repo Installation

Since the repo is private on GitHub, you will need to follow one of the options to create a new project:

### Options 1 (CLI Authentication)

1) Download and install GitHub CLI ```https://github.com/cli/cli/releases```
2) Open a new CMD prompt (the above installation modifies the Windows PATH environment)
3) Switch to your projects directory ```cd c:\your-projects-directory```
4) Authenticate into GitHub ```gh auth login```
5) Follow the prompts to login
6) Clone the Application Works project ```gh repo clone arajcany/ApplicationWorks <YourProjectName>```
7) Navigate into the newly created project directory ```cd c:\your-projects-directory\<YourProjectName>```
8) Navigate into the newly created project directory ```cd c:\your-projects-directory\<YourProjectName>\.git```
9) Empty out the contents of this directory (i.e. this removes all references to the original ApplicationWorks project)
10) Run Composer install/upgrade
11) Add this new project to GitHub

You can now proceed to configuring IIS.

### Options 2 (GitHub Download)

1) In a browser, navigate to https://github.com/arajcany/ApplicationWorks
2) Download the source code
3) Expand to a directory on your computer
4) Run Composer install/upgrade
5) Add this new project to GitHub

You can now proceed to configuring IIS.

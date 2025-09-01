<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * ResetPassword command.
 * @property UsersTable $Users
 */
class ResetPasswordCommand extends AppCommand
{
    private Table|UsersTable $Users;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->Users = TableRegistry::getTableLocator()->get('Users');

        $userIdentifier = $io->ask('Please type the Username or Email of the Password you would like to reset.');

        if (strlen($userIdentifier) < 1) {
            $io->abort(__("Please enter a valid Username or Email... Bye!"), 1);
        }

        /**
         * @var User $user
         */
        $user = $this->Users->find('all')
            ->where(['OR' => ['username' => $userIdentifier, 'email' => $userIdentifier]])
            ->first();

        if (!$user) {
            $io->abort(__("Sorry, User {0} was not found.", $userIdentifier), 1);
        }

        $newPassword = $io->ask(__("Please type the new Password."));

        $user->password = $newPassword;
        $user->password_expiry = (new DateTime())->subYears(1);;
        $result = $this->Users->save($user);

        if ($result) {
            $io->abort(__("The password for {0} was successfully changed.", $userIdentifier), 1);
        } else {
            $io->abort(__("The was an issue when changing the password for {0}. Please try again.", $userIdentifier), 1);
        }

    }
}

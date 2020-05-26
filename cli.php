<?php
/**
 * Created by PhpStorm.
 * Date 26.05.2020
 * @author Alerinos
 * @github https://github.com/Alerinos
 */

/*
 * USE THIS AS A CLI EXAMPLE: php cli.php
 * Remember to set the character encoding
 * May two databases have to have the same file structures
 * keep account, player and other tables in one database
 * Make a copy of the data, do not operate live. It's best to create two new bases and upload two servers there. The script says "secondBase" so be careful. MAKE A BACKUP COPY
 */

$migration = new Migration;

// Coding Type
$migration->charset = 'gb2312_chinese_ci';

// Table settings
$migration->account('account', 'id', ['login', 'password', 'social_id', 'email', 'securitycode', 'status', 'availDt', 'create_time', 'last_play', 'gold_expire', 'silver_expire', 'safebox_expire', 'autoloot_expire', 'fish_mind_expire', 'marriage_fast_expire', 'money_drop_rate_expire', 'real_name', 'question1', 'answer1', 'question2', 'answer2', 'cash']);
$migration->player('player', 'id', 'account', ['name', 'job', 'kingdom', 'voice', 'dir', 'x', 'y', 'z', 'map_index', 'exit_x', 'exit_y', 'exit_map_index', 'hp', 'mp', 'stamina', 'random_hp', 'random_sp', 'playtime', 'level', 'level_step', 'st', 'ht', 'dx', 'iq', 'exp', 'gold', 'stat_point', 'skill_point', 'quickslot', 'ip', 'part_main', 'part_base', 'part_hair', 'part_acce', 'skill_group', 'skill_level', 'alignment', 'last_play', 'change_name', 'mobile', 'sub_skill_point', 'stat_reset_count', 'horse_hp', 'horse_stamina', 'horse_level', 'horse_hp_droptime', 'horse_riding', 'horse_skill_point']);

// Database connection settings
$migration->firstBase('localhost', 'migration', 'migration', 'migration_1');
$migration->secondBase('localhost', 'migration', 'migration', 'migration_2');

// List of tables to migrate
// The account and player table is automatically added
//$migration->add('item', 'owner_id', ['window', 'pos', 'count', 'vnum', 'socket0', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6']);
$migration->add('guild', 'master', ['name', 'sp', 'level', 'exp', 'skill_point', 'skill', 'win', 'draw', 'loss', 'ladder_point', 'gold']);
$migration->add('quest', 'dwPID', ['szName', 'szState', 'lValue']);

// Migration Process
$migration->start();




class Migration {

    public string $charset = 'utf8';

    private PDO $fb;
    private PDO $sb;
    private PDO $mb;

    private array $table;
    private array $account;
    private array $player;

    private function pdo(string $ip, string $user, string $pass, string $name): PDO
    {
        $charset = "utf8";
        return new \PDO("mysql:host=$ip;dbname=$name;charset=$charset", $user, $pass);
    }

    /**
     * Database address of the first server
     * @param string $ip server IP address
     * @param string $user User Name
     * @param string $pass user password
     * @param string $name database name
     */
    public function firstBase(string $ip, string $user, string $pass, string $name): void
    {
        $this->fb = $this->pdo($ip, $user, $pass, $name);
    }

    /**
     * Second server database address
     * @param string $ip server IP address
     * @param string $user User Name
     * @param string $pass user password
     * @param string $name database name
     */
    public function secondBase(string $ip, string $user, string $pass, string $name): void
    {
        $this->sb = $this->pdo($ip, $user, $pass, $name);
    }

    /**
     * All data will be transferred there
     * @param string $ip server IP address
     * @param string $user User Name
     * @param string $pass user password
     * @param string $name database name
     */
    public function migrationBase(string $ip, string $user, string $pass, string $name): void
    {
        $this->mb = $this->pdo($ip, $user, $pass, $name);
    }


    /**
     * Add a table to move
     * @param string $table name of the table to be migrated
     * @param string $id column name with character ID
     * @param array $column list of columns to be moved
     */
    public function add(string $table, string $id, array $column): void
    {
        $this->table[] = [
            'table'     => $table,
            'id'        => $id,
            'column'    => $column
        ];
    }

    /**
     * The function defines columns in the account table
     * @param string $name account table name
     * @param string $id account id
     * @param array $column list of columns to move
     */
    public function account(string $name, string $id, array $column): void
    {
        $this->account = [
            'name'  => $name,
            'id'    => $id,
            'column'=> $column,
        ];
    }

    /**
     * The function defines columns in the player table
     * @param string $name player table name
     * @param string $id player id
     * @param string $account column with ID account
     * @param array $column list of columns to move
     */
    public function player(string $name, string $id, string $account, array $column): void
    {
        $this->player = [
            'name'      => $name,
            'id'        => $id,
            'account'   => $account,
            'column'    => $column,
        ];
    }

    public function start(): void
    {
        $this->text("[account] I am migrating the 'account' table", 'yellow');

        // Account
        $column = array_merge([$this->account['id']], $this->account['column']);
        $column = implode(',', $column);
        $db = $this->fb->prepare("SELECT $column FROM {$this->account['name']}");
        $db->execute();
        foreach ($db as $ra){
            $account = 0;       // New Account ID
            $player = 0;        // New Player ID
            $player_index = []; // Player index

            // Check account
//            $db = $this->sb->prepare("SELECT {$this->account['id']} FROM {$this->account['name']} WHERE {$this->account['id']} = :id");
//            $db->execute(['id' => $this->account['id']]);
//            print_r($db->fetch());

            // Create account
            $column = implode(',', $this->account['column']);
            $values = array_map(function ($v) {
                return ":".$v;
            }, $this->account['column']);
            $values = implode(', ', $values);
            $db = $this->sb->prepare("INSERT INTO {$this->account['name']} ($column) VALUES ($values)");
            foreach ($this->account['column'] as $r){
                $db->bindValue($r, $ra[$r]);
            }
            $db->execute();
            $account = $this->sb->lastInsertId();
            $this->text("[account] Transfer account {$ra[$this->account['id']]}, new ID $account", 'green');


            // Search Player
            $column = array_merge([$this->player['id'], $this->player['account']], $this->player['column']);
            $column = implode(',', $column);
            $db = $this->fb->prepare("SELECT $column FROM {$this->player['name']} WHERE {$this->player['account']} LIKE :account");
            $db->execute([ 'account' => $ra[$this->account['id']] ]);

            $this->text("[player] I am transferring {$db->rowCount()} characters", 'yellow');
            foreach ($db as $rp){
                // Create Player
                $column = implode(',', array_merge([$this->player['account']],$this->player['column']));
                $values = array_map(function ($v) {
                    return ":".$v;
                }, array_merge([$this->player['account']],$this->player['column']));
                $values = implode(', ', $values);
                $db = $this->sb->prepare("INSERT INTO {$this->player['name']} ($column) VALUES ($values)");
                foreach ($this->player['column'] as $r){
                    $db->bindValue($r, $rp[$r]);
                }
                $db->bindValue('account', $account);
                $db->execute();
                $player = $this->sb->lastInsertId();
                $player_index[] = $player;

                $this->text("[player] I am transferring form player id {$rp[$this->player['id']]} of account $account, new ID $player", 'green');


                // I create dynamic tables
                foreach ($this->table as $rdt){
                    // select
                    $column = implode(',', $rdt['column']);
                    $db = $this->fb->prepare("SELECT {$column} FROM {$rdt['table']} WHERE {$rdt['id']} LIKE :player");
                    $db->execute(['player' => $rp[ $this->player['id'] ] ]);
                    if($db->rowCount()){
                        $this->text("[{$rdt['table']}] transfer {$db->rowCount()} pcs", 'yellow');
                    }
                    foreach ($db as $rt){
                        $column = implode(',', array_merge([$rdt['id']], $rdt['column']));
                        $values = array_map(function ($v) {
                            return ":".$v;
                        }, array_merge([$rdt['id']], $rdt['column']) );
                        $values = implode(', ', $values);

                        $db = $this->sb->prepare ("INSERT INTO {$rdt['table']} ($column) VALUES ($values)");
                        foreach ($rdt['column'] as $r){
                            $db->bindValue($r, $rt[$r]);
                        }

                        $db->bindValue($rdt['id'], $player);
                        $db->execute();

                        $lastID = $this->sb->lastInsertId();
//                        $this->text("[{$rdt['table']}] I transfer to ID $lastID of player ID $player account $account", 'green');
                        $this->text("[{$rdt['table']}] I transfer account $account for character $player", 'green');
                    }
                }

            }

            // Create player_index
            if(!isset($player_index[0])) $player_index[0] = 0;
            if(!isset($player_index[1])) $player_index[1] = 0;
            if(!isset($player_index[2])) $player_index[2] = 0;
            if(!isset($player_index[3])) $player_index[3] = 0;
            if(!isset($player_index[4])) $player_index[4] = 0;

            // FIXME: Find empire and copy
            $db = $this->sb->prepare("INSERT INTO `player_index`(`id`, `pid1`, `pid2`, `pid3`, `pid4`, `pid5`, `empire`) VALUES ($account, {$player_index[0]}, {$player_index[1]}, {$player_index[2]}, {$player_index[3]}, {$player_index[4]}, 0)");
            $db->execute();
        }

    }

    public function text(string $content = "", string $color = '0'): void
    {
        // https://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
        switch ($color){
            default: $color = "0"; break;
            case 'black': $color = "0;30"; break;
            case 'red': $color = "0;31"; break;
            case 'green': $color = "0;32"; break;
            case 'yellow': $color = "1;33"; break;
        }

        $content = "\033[{$color}m$content \033[0m\n\r";

        echo $content;
    }
}
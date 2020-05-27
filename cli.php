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
$migration->duplicat = '_';

// Table settings
$migration->account('account', 'id', 'login', ['password', 'social_id', 'email', 'status', 'availDt', 'create_time', 'last_play', 'gold_expire', 'silver_expire', 'safebox_expire', 'autoloot_expire', 'fish_mind_expire', 'marriage_fast_expire', 'money_drop_rate_expire', 'real_name', 'coins', 'game_coins', 'web_admin', 'register_ip', 'last_ip', 'action_token', 'action_type', 'action_time', 'action_text', 'drs', 'enabled_time', 'cash']);
$migration->player('player', 'id', 'account_id', ['name', 'job', 'voice', 'dir', 'x', 'y', 'z', 'map_index', 'exit_x', 'exit_y', 'exit_map_index', 'hp', 'mp', 'stamina', 'random_hp', 'random_sp', 'playtime', 'level', 'level_step', 'st', 'ht', 'dx', 'iq', 'exp', 'gold', 'stat_point', 'skill_point', 'quickslot', 'ip', 'part_main', 'part_base', 'part_hair', 'part_sash', 'skill_group', 'skill_level', 'alignment', 'last_play', 'change_name', 'mobile', 'sub_skill_point', 'stat_reset_count', 'horse_hp', 'horse_stamina', 'horse_level', 'horse_hp_droptime', 'horse_riding', 'horse_skill_point', 'imageid', 'combat_zone_rank', 'combat_zone_points', 'extend_inven', 'gaya', 'bead', 'pz']);

// Database connection settings
$migration->firstBase('localhost', 'migration', 'migration', 'migration_1');
$migration->secondBase('localhost', 'migration', 'migration', 'migration_2');

// List of tables to migrate
// The account and player table is automatically added
$migration->add('affect', 'dwPID', ['bType', 'bApplyOn', 'lApplyValue', 'dwFlag', 'lDuration', 'lSPCost']);
$migration->add('guild', 'master', ['name', 'sp', 'level', 'exp', 'skill_point', 'skill', 'win', 'draw', 'loss', 'ladder_point', 'gold', 'dungeon_ch', 'dungeon_map', 'dungeon_cooldown', 'dungeon_start']);
$migration->add('item', 'owner_id', ['window', 'pos', 'count', 'vnum', 'bind', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6'], "((`window` = 'INVENTORY' OR `window` = 'EQUIPMENT' or `window` = 'DRAGON_SOUL_INVENTORY' or `window` = 'BELT_INVENTORY' or `window` = 'GROUND') and `owner_id` = :player) or ((`window` = 'SAFEBOX' OR `window` = 'MALL') and `owner_id` = :account)");
$migration->add('player_gift', 'owner_id', ['date_add', 'date_get', 'status', 'from', 'reason', 'vnum', 'count', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6', 'applytype0', 'applyvalue0', 'applytype1', 'applyvalue1', 'applytype2', 'applyvalue2', 'applytype3', 'applyvalue3', 'applytype4', 'applyvalue4', 'applytype5', 'applyvalue5', 'applytype6', 'applyvalue6', 'applytype7', 'applyvalue7']);
$migration->add('player_shop', 'player_id', ['shop_vid', 'item_count', 'name', 'status', 'map_index', 'x', 'y', 'z', 'date', 'date_close', 'ip', 'gold', 'cash', 'channel', 'npc', 'npc_decoration']);
$migration->add('quest', 'dwPID', ['szName', 'szState', 'lValue']);

// Migration Process
$migration->start();

class Migration {

    public string $charset = 'utf8';
    public string $duplicat = '_duplicat';

    private PDO $fb;
    private PDO $sb;
    private PDO $mb;

    private array $table;
    private array $account;
    private array $player;


    private function pdo(string $ip, string $user, string $pass, string $name): PDO
    {
        $charset = "utf8";
        $charset = "gb2312";
        $dbh =  new \PDO("mysql:host=$ip;dbname=$name", $user, $pass);
        $dbh->exec('SET CHARACTER SET utf8');
        $dbh->query("SET NAMES utf8");

        return $dbh;
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
    public function add(string $table, string $id, array $column, string $where = null): void
    {
        $this->table[] = [
            'table'     => $table,
            'id'        => $id,
            'column'    => $column,
            'where'     => $where,
        ];
    }

    /**
     * The function defines columns in the account table
     * @param string $name account table name
     * @param string $id account id
     * @param array $column list of columns to move
     */
    public function account(string $name, string $id, string $login, array $column): void
    {
        $this->account = [
            'name'  => $name,
            'id'    => $id,
            'column'=> $column,
            'login' => $login,
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

    public function insert(string $table, array $bind, bool $debug = false): int
    {
        $column = array_map(function ($v) { return "`$v`"; }, array_keys($bind));
        $column = implode(', ', $column);
        $values = array_map(function ($v) {
            return ":".$v;
        }, array_keys($bind));
        $values = implode(', ', $values);

        $db = $this->sb->prepare ("INSERT INTO `$table` ($column) VALUES ($values)");
        $db->execute($bind);

        if($debug){
            $values = [];
            foreach ($bind as $r => $v){ $values[] = "'$v'"; }
            $values = implode(",", $values);

            echo "INSERT INTO `$table` ($column) VALUES ($values)";
        }

        return $this->sb->lastInsertId();
    }

    public function start(): void
    {
        $this->text("[account] I am migrating the 'account' table", 'yellow');

        // Account
        $column = array_merge([$this->account['id'], $this->account['login']], $this->account['column']);
        $column = implode(',', $column);
        $db = $this->fb->prepare("SELECT $column FROM {$this->account['name']}");
        $db->execute();
        foreach ($db as $ra){
            $account = 0;       // New Account ID
            $lastAccount = $ra[$this->account['id']];
            $player = 0;        // New Player ID
            $player_index = []; // Player index

            // Check account
            $db = $this->sb->prepare("SELECT `{$this->account['login']}` FROM {$this->account['name']} WHERE `{$this->account['login']}` = '{$ra[$this->account['login']]}' ");
            $db->execute();
            if($al = $db->fetch()){
                $login = $al[$this->account['login']].$this->duplicat;
            }else{
                $login = $ra[$this->account['login']];
            }

            // Create account
//            $column = implode(',', array_merge([$this->account['login']], $this->account['column']));
//            $values = array_map(function ($v) {
//                return ":".$v;
//            }, array_merge([$this->account['login']], $this->account['column']));
//            $values = implode(', ', $values);
//            $db = $this->sb->prepare("INSERT INTO {$this->account['name']} ($column) VALUES ($values)");
//            foreach ($this->account['column'] as $r){
//                $db->bindValue($r, $ra[$r]);
//            }
//            $db->bindValue('login', $login);

// -- fix code
            $column = implode(',', $this->account['column']);
            $values = array_map(function ($v) use ($ra) {
                if($ra[$v]){
                    return '"'.$ra[$v].'"';
                }else{
                    return 'FALSE';
                }
            }, $this->account['column']);
            $values = implode(',', $values);
            $db = $this->sb->prepare("INSERT INTO {$this->account['name']} (login, $column) VALUES ('$login', $values )");
// -- fix code
            $db->execute();
            $account = $this->sb->lastInsertId();
            if(!$account) {     // debug

                echo PHP_EOL;
                echo "INSERT INTO {$this->account['name']} (login, $column) VALUES ('$login', $values )".PHP_EOL.PHP_EOL;


//                print_r($ra);
                print_r($this->sb->errorInfo());
//                echo "INSERT INTO {$this->account['name']} ($column) VALUES ($values)".PHP_EOL;
//                foreach ($this->account['column'] as $r){
//                    echo ":$r => {$ra[$r]}".PHP_EOL;
//                }
//                echo ":login => {$login}".PHP_EOL;
//                echo "SELECT `{$this->account['login']}` FROM {$this->account['name']} WHERE `{$this->account['login']}` = '{$ra[$this->account['login']]}'".PHP_EOL;
                $this->text("Account ID: $lastAccount has not been created, character addition skipped.", 'red');

                exit;   // END DEBUG
            }else{


            $this->text("[account] Transfer account $lastAccount, new ID $account", 'green');


            // Search Player
            $column = array_merge([$this->player['id'], $this->player['account']], $this->player['column']);
            $column = implode(',', $column);
            $db = $this->fb->prepare("SELECT $column FROM {$this->player['name']} WHERE {$this->player['account']} LIKE :account");
            $db->execute([ 'account' => $ra[$this->account['id']] ]);

            $this->text("[player] I am transferring {$db->rowCount()} characters", 'yellow');
            foreach ($db as $rp){
                $lastPlayer = $rp[$this->player['id']];
                $playerName = $rp['name'];
                $lastPlayerName = $rp['name'];

                // check player name
                $db = $this->sb->prepare("SELECT `name` FROM {$this->player['name']} WHERE `name` = '$playerName' ");
                $db->execute();
                if($pn = $db->fetch()){
                    $playerName = $pn['name'].$this->duplicat;
                }

                // Create Player
                $column = implode(',', array_merge([$this->player['account']],$this->player['column']));
                $values = array_map(function ($v) {
                    return ":".$v;
                }, array_merge([$this->player['account']],$this->player['column']));
                $values = implode(', ', $values);
                $db = $this->sb->prepare("INSERT INTO {$this->player['name']} ($column) VALUES ($values)");
                foreach ($this->player['column'] as $r){
                    if($r == 'name'){
                        $db->bindValue($r, $playerName);
                    }else
                        $db->bindValue($r, $rp[$r]);
                }
                $db->bindValue($this->player['account'], $account);
                $db->execute();
                $player = $this->sb->lastInsertId();
                $player_index[] = $player;

                $this->text("[player] I am transferring form player id {$rp[$this->player['id']]} of account $account, new ID $player", 'green');

                // I create dynamic tables
                foreach ($this->table as $rdt){
                    // select
                    if($rdt['where']){
                        $where = $rdt['where'];
                    }else{
                        $where = "{$rdt['id']} LIKE :player";
                    }

                    $column = array_map(function ($v){return "`$v`"; }, $rdt['column']);
                    $column = implode(', ', $column);
                    $db = $this->fb->prepare("SELECT {$column} FROM {$rdt['table']} WHERE $where");
                    if($rdt['where']) $db->bindValue('account', $lastAccount);
                    $db->bindValue('player', $rp[ $this->player['id']]);
                    $db->execute();
                    if($db->rowCount()){
                        $this->text("[{$rdt['table']}] transfer {$db->rowCount()} pcs", 'yellow');
                    }
                    foreach ($db as $rt){
                        $values = [];
                        foreach ($rdt['column'] as $r){ $values[$r] = $rt[$r]; }
                        $values[$rdt['id']] = $player;
                        $id = $this->insert($rdt['table'], $values);
                        $this->text("[{$rdt['table']}] Record with ID $id added. I transfer account $account for character $player", 'green');
                    }
                }

                // player_shop
                $db = $this->fb->prepare("SELECT * FROM `player_shop` WHERE `player_id` LIKE $lastPlayer");
                $db->execute();
                if($ss = $db->fetch()) {
                    $db = $this->sb->prepare("
                    INSERT INTO `player_shop`(`shop_vid`, `player_id`, `item_count`, `name`, `status`, `map_index`, `x`, `y`, `z`, `date`, `date_close`, `ip`, `gold`, `cash`, `channel`, `npc`, `npc_decoration`) 
                    VALUES  (0,{$player},{$ss['item_count']},'{$ss['name']}','{$ss['status']}',{$ss['map_index']},{$ss['x']},{$ss['y']},{$ss['z']},'{$ss['date']}','{$ss['date_close']}','{$ss['ip']}',{$ss['gold']},{$ss['cash']},{$ss['channel']},{$ss['npc']},{$ss['npc_decoration']})
                    ");
                    $db->execute();
                    $shopID = $this->sb->lastInsertId();
//print_r($ss);
//                    echo "SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['shop_vid']}"; exit();
//echo "SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['id']}"; exit;
                    $db = $this->fb->prepare("SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['id']}");
                    $db->execute();
                    foreach ($db as $psi){
                        $db = $this->sb->prepare("INSERT INTO `player_shop_items`
                            (`shop_id`, `player_id`, `vnum`, `count`, `pos`, `display_pos`, `price`, `socket0`, `socket1`, `socket2`, `socket3`, `socket4`, `socket5`, `attrtype0`, `attrvalue0`, `attrtype1`, `attrvalue1`, `attrtype2`, `attrvalue2`, `attrtype3`, `attrvalue3`, `attrtype4`, `attrvalue4`, `attrtype5`, `attrvalue5`, `attrtype6`, `attrvalue6`, `applytype0`, `applyvalue0`, `applytype1`, `applyvalue1`, `applytype2`, `applyvalue2`, `applytype3`, `applyvalue3`, `applytype4`, `applyvalue4`, `applytype5`, `applyvalue5`, `applytype6`, `applyvalue6`, `applytype7`, `applyvalue7`) VALUES 
                            ($shopID,$player,{$psi['vnum']},{$psi['count']},{$psi['pos']},{$psi['display_pos']},{$psi['price']},{$psi['socket0']}, {$psi['socket1']}, {$psi['socket2']}, {$psi['socket3']}, {$psi['socket4']}, {$psi['socket5']}, {$psi['attrtype0']}, {$psi['attrvalue0']}, {$psi['attrtype1']}, {$psi['attrvalue1']}, {$psi['attrtype2']}, {$psi['attrvalue2']}, {$psi['attrtype3']}, {$psi['attrvalue3']}, {$psi['attrtype4']}, {$psi['attrvalue4']}, {$psi['attrtype5']}, {$psi['attrvalue5']}, {$psi['attrtype6']}, {$psi['attrvalue6']}, {$psi['applytype0']}, {$psi['applyvalue0']}, {$psi['applytype1']}, {$psi['applyvalue1']}, {$psi['applytype2']}, {$psi['applyvalue2']}, {$psi['applytype3']}, {$psi['applyvalue3']}, {$psi['applytype4']}, {$psi['applyvalue4']}, {$psi['applytype5']}, {$psi['applyvalue5']}, {$psi['applytype6']}, {$psi['applyvalue6']}, {$psi['applytype7']}, {$psi['applyvalue7']})");

                        $db->execute();
                    }
                }


                // player_shop
//                $db = $this->fb->prepare("SELECT * FROM `player_shop` WHERE `player_id` LIKE $lastPlayer");
//                $db->execute();
//                if($ss = $db->fetch()) {
//                    $db = $this->sb->prepare("
//                    INSERT INTO `player_shop`(`id`, `player_id`, `item_count`, `name`, `status`, `map_index`, `x`, `y`, `z`, `date`, `date_close`, `ip`, `gold`, `cash`, `channel`, `npc`, `npc_decoration`)
//                    VALUES  (0,{$player},{$ss['item_count']},'{$ss['name']}','{$ss['status']}',{$ss['map_index']},{$ss['x']},{$ss['y']},{$ss['z']},'{$ss['date']}','{$ss['date_close']}','{$ss['ip']}',{$ss['gold']},{$ss['cash']},{$ss['channel']},{$ss['npc']},{$ss['npc_decoration']})
//                    ");
//                    $db->execute();
//                    $shopID = $this->sb->lastInsertId();
////print_r($ss);
////                    echo "SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['shop_vid']}"; exit();
////echo "SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['shop_vid']}"; exit;
//                    $db = $this->fb->prepare("SELECT * FROM `player_shop_items` WHERE `shop_id` LIKE {$ss['shop_vid']}");
//                    $db->execute();
//                    foreach ($db as $psi){
//                        $db = $this->sb->prepare("INSERT INTO `player_shop_items`
//                            (`shop_id`, `player_id`, `vnum`, `count`, `pos`, `display_pos`, `price`, `socket0`, `socket1`, `socket2`, `socket3`, `socket4`, `socket5`, `attrtype0`, `attrvalue0`, `attrtype1`, `attrvalue1`, `attrtype2`, `attrvalue2`, `attrtype3`, `attrvalue3`, `attrtype4`, `attrvalue4`, `attrtype5`, `attrvalue5`, `attrtype6`, `attrvalue6`, `applytype0`, `applyvalue0`, `applytype1`, `applyvalue1`, `applytype2`, `applyvalue2`, `applytype3`, `applyvalue3`, `applytype4`, `applyvalue4`, `applytype5`, `applyvalue5`, `applytype6`, `applyvalue6`, `applytype7`, `applyvalue7`) VALUES
//                            ($shopID,$player,{$psi['vnum']},{$psi['count']},{$psi['pos']},{$psi['display_pos']},{$psi['price']},{$psi['socket0']}, {$psi['socket1']}, {$psi['socket2']}, {$psi['socket3']}, {$psi['socket4']}, {$psi['socket5']}, {$psi['attrtype0']}, {$psi['attrvalue0']}, {$psi['attrtype1']}, {$psi['attrvalue1']}, {$psi['attrtype2']}, {$psi['attrvalue2']}, {$psi['attrtype3']}, {$psi['attrvalue3']}, {$psi['attrtype4']}, {$psi['attrvalue4']}, {$psi['attrtype5']}, {$psi['attrvalue5']}, {$psi['attrtype6']}, {$psi['attrvalue6']}, {$psi['applytype0']}, {$psi['applyvalue0']}, {$psi['applytype1']}, {$psi['applyvalue1']}, {$psi['applytype2']}, {$psi['applyvalue2']}, {$psi['applytype3']}, {$psi['applyvalue3']}, {$psi['applytype4']}, {$psi['applyvalue4']}, {$psi['applytype5']}, {$psi['applyvalue5']}, {$psi['applytype6']}, {$psi['applyvalue6']}, {$psi['applytype7']}, {$psi['applyvalue7']}");
//
//                        $db->execute();
//                    }
//                }


            }

            // safebox
            $db = $this->fb->prepare("SELECT `account_id`, `size`, `password`, `gold` FROM `safebox` WHERE `account_id` LIKE :account");
            $db->execute(['account' => $lastAccount]);
            if($sf = $db->fetch()){
                if(!isset($sf['password'])) $sf['password'] = "";
                $db = $this->sb->prepare("INSERT INTO `safebox`(`account_id`, `size`, `password`, `gold`) VALUES ($account,{$sf['size']}, '{$sf['password']}',{$sf['gold']})");
                $db->execute();
            }

            $db = $this->fb->prepare("SELECT * FROM `player_index` WHERE `id` LIKE $lastAccount");
            $db->execute();
            $pi = $db->fetch();

            // Create player_index
            if(!isset($player_index[0])) $player_index[0] = 0;
            if(!isset($player_index[1])) $player_index[1] = 0;
            if(!isset($player_index[2])) $player_index[2] = 0;
            if(!isset($player_index[3])) $player_index[3] = 0;
            if(!isset($player_index[4])) $player_index[4] = 0;
            if(!isset($pi['empire'])) $pi['empire'] = 1;

            // FIXME: Find empire and copy
            $db = $this->sb->prepare("INSERT INTO `player_index`(`id`, `pid1`, `pid2`, `pid3`, `pid4`, `pid5`, `empire`) VALUES ($account, {$player_index[0]}, {$player_index[1]}, {$player_index[2]}, {$player_index[3]}, {$player_index[4]}, {$pi['empire']})");
            $db->execute();

            }

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
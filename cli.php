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

// Migration Process
$migration->newStart();

class Migration {

    const FIRST_BASE = 'first';
    const SECOND_BASE = 'second';

    public string $charset = 'utf8';
    public string $duplicat = '_duplicat';

    private PDO $fb;
    private PDO $sb;
    private PDO $mb;

    private object $base;

    private array $table;
    private array $account;
    private array $player;

    private int $lastAccount;
    private int $newAccount;
    private int $lastPlayer;
    private int $newPlayer;

    private string $playerName;

    private array $accounts = [];
    private array $players = [];

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

    public function base(string $name = 'first'): self
    {
        switch ($name){
            case 'first':
                $this->base = $this->fb;
                break;

            case 'second':
                $this->base = $this->sb;
                break;
        }

        return $this;
    }

    /**
     * @param string $table
     * @param array $bind
     * @param bool $debug
     * @return int
     */
    public function insert(string $table, array $bind, bool $debug = false): int
    {
        $column = array_map(function ($v) { return "`$v`"; }, array_keys($bind));
        $column = implode(', ', $column);
        $values = array_map(function ($v) {
            return ":".$v;
        }, array_keys($bind));
        $values = implode(', ', $values);

        $db = $this->base->prepare ("INSERT INTO `$table` ($column) VALUES ($values)");
        $db->execute($bind);

        if($debug){
            $values = [];
            foreach ($bind as $r => $v){ $values[] = "'$v'"; }
            $values = implode(",", $values);

            echo "INSERT INTO `$table` ($column) VALUES ($values)";
        }

        return $this->base->lastInsertId();
    }

    /**
     * @param string $table
     * @param string $where
     * @param array $bind
     * @return array
     */
    public function select(string $table, array $column = [], string $where = "", array $bind = []): array
    {
        if(count($column) == 0)
            $column = "*";
        else
            $column = implode(', ', $column);

        if($where)
            $where = "WHERE $where";

        $db = $this->base->prepare("SELECT $column FROM `$table` $where");
        $db->execute($bind);
        return $db->fetchAll();
    }

    public function newStart(): void
    {
        $this->text("", 'yellow');

        $account = $this->base(self::FIRST_BASE)->select('account');
        foreach ($account as $a){
            $this->lastAccount = $a['id'];

            // Create new account
            $this->newAccount = $this->base(self::SECOND_BASE)->insert('account', [
                'login'         => $a['login'].$this->duplicat,
                'password'      => $a['password'],
                'email'         => $a['email'],
                'status'        => $a['status'],
                'coins'         => $a['coins'],

                'availDt'                   => $a['availDt'],
                'create_time'               => $a['create_time'],
                'last_play'                 => $a['last_play'],
                'gold_expire'               => $a['gold_expire'],
                'silver_expire'             => $a['silver_expire'],
                'safebox_expire'            => $a['safebox_expire'],
                'autoloot_expire'           => $a['autoloot_expire'],
                'fish_mind_expire'          => $a['fish_mind_expire'],
                'marriage_fast_expire'      => $a['marriage_fast_expire'],
                'money_drop_rate_expire'    => $a['money_drop_rate_expire'],

                'last_ip'       => $a['last_ip'],
                'last_login'    => $a['last_login'],
                'action_token'  => $a['action_token'],
                'action_type'   => $a['action_type'],
                'action_time'   => $a['action_time'],
                'action_text'   => $a['action_text'],
                'drs'           => $a['drs'],
                'enabled_time'  => $a['enabled_time'],
                'cash'          => $a['cash'],
            ]);

            // list of all accounts
            $this->accounts[$this->lastAccount] = $this->newAccount;

            if(!$this->newAccount){
                $this->text('Error', 'red');
            }else{
                $player_index = [];
                $player = $this->base(self::FIRST_BASE)->select('player', [], "`account_id` = :account", ['account' => $this->lastAccount]);
                foreach ($player as $p){
                    $this->lastPlayer = $p['id'];
                    $this->playerName = $p['name'];

                    // Create new player
                    $column = ['name', 'job', 'voice', 'dir', 'x', 'y', 'z', 'map_index', 'exit_x', 'exit_y', 'exit_map_index', 'hp', 'mp', 'stamina', 'random_hp', 'random_sp', 'playtime', 'level', 'level_step', 'st', 'ht', 'dx', 'iq', 'exp', 'gold', 'stat_point', 'skill_point', 'quickslot', 'ip', 'part_main', 'part_base', 'part_hair', 'part_sash', 'skill_group', 'skill_level', 'alignment', 'last_play', 'change_name', 'mobile', 'sub_skill_point', 'stat_reset_count', 'horse_hp', 'horse_stamina', 'horse_level', 'horse_hp_droptime', 'horse_riding', 'horse_skill_point', 'imageid', 'combat_zone_rank', 'combat_zone_points', 'extend_inven', 'gaya', 'bead', 'pz'];
                    $column = array_combine($column, array_map(function ($v) use ($p) { return $p[$v]; }, $column));
                    $this->newPlayer = $this->base(self::SECOND_BASE)->insert('player', array_merge([
                        'account_id'    => $this->newAccount,
                        'name'          => $this->playerName.$this->duplicat,
                    ], $column));

                    $this->text("[player] {$this->newPlayer}");

                    // List of all players
                    $this->players[$this->lastPlayer] = $this->newPlayer;

                    // Player index
                    $player_index[] = $this->newPlayer;

                    // affect
                    $affect = $this->base(self::FIRST_BASE)->select('affect', [], "`dwPID` = :player", ['player' => $this->lastPlayer]);
                    foreach ($affect as $af){
                        $column = ['bType', 'bApplyOn', 'lApplyValue', 'dwFlag', 'lDuration', 'lSPCost'];
                        $column = array_combine($column, array_map(function ($v) use ($af) { return $af[$v]; }, $column));
                        $this->base(self::SECOND_BASE)->insert('affect', array_merge([
                            'dwPID' => $this->newPlayer
                        ], $column));

                        $this->text("[affect] player {$this->newPlayer}");
                    }

                    // item
                    $item = $this->base(self::FIRST_BASE)->select('item', [], "((`window` = 'INVENTORY' OR `window` = 'EQUIPMENT' or `window` = 'DRAGON_SOUL_INVENTORY' or `window` = 'BELT_INVENTORY' or `window` = 'GROUND') and `owner_id` = :player) or ((`window` = 'SAFEBOX' OR `window` = 'MALL') and `owner_id` = :account)", ['player' => $this->lastPlayer, 'account' => $this->lastAccount]);
                    foreach ($item as $it){
                        $column = ['window', 'pos', 'count', 'vnum', 'bind', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6'];
                        $column = array_combine($column, array_map(function ($v) use ($it) { return $it[$v]; }, $column));
                        $this->base(self::SECOND_BASE)->insert('item', array_merge([
                            'owner_id' => ($it['window'] == 'MALL' OR $it['window'] == 'SAFEBOX') ? $this->newAccount : $this->newPlayer
                        ], $column));

                        $this->text("[item] player {$this->newPlayer}");
                    }


                    // #1292 - Incorrect datetime value: '0000-00-00 00:00:00' for column 'date_get' at row 1
                    // player_gift
                    $player_gift = $this->base(self::FIRST_BASE)->select('player_gift', [], "`owner_id` = :player", ['player' => $this->lastPlayer]);
                    foreach ($player_gift as $pg){
                        $column = ['date_add', 'date_get', 'status', 'from', 'reason', 'vnum', 'count', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6', 'applytype0', 'applyvalue0', 'applytype1', 'applyvalue1', 'applytype2', 'applyvalue2', 'applytype3', 'applyvalue3', 'applytype4', 'applyvalue4', 'applytype5', 'applyvalue5', 'applytype6', 'applyvalue6', 'applytype7', 'applyvalue7'];
                        $column = array_combine($column, array_map(function ($v) use ($pg) { return $pg[$v]; }, $column));
                        $id = $this->base(self::SECOND_BASE)->insert('player_gift', array_merge([
                            'owner_id' => $this->newPlayer
                        ], $column));

//                        if(!$id){
//                            $this->base(self::SECOND_BASE)->insert('player_gift', array_merge([
//                                'owner_id' => $this->newPlayer
//                            ], $column), true); exit();
//                        }

                        $this->text("[player_gift] player {$this->newPlayer}");
                    }

                    // player_shop
                    $player_shop = $this->base(self::FIRST_BASE)->select('player_shop', [], "`player_id` = :player", ['player' => $this->lastPlayer]);
                    foreach ($player_shop as $ps){
                        $column = ['shop_vid', 'item_count', 'name', 'status', 'map_index', 'x', 'y', 'z', 'date', 'date_close', 'ip', 'gold', 'cash', 'channel', 'npc', 'npc_decoration'];
                        $column = array_combine($column, array_map(function ($v) use ($ps) { return $ps[$v]; }, $column));
                        $shop = $this->base(self::SECOND_BASE)->insert('player_shop', array_merge([
                            'player_id' => $this->newPlayer
                        ], $column));

                        $this->text("[player_shop] player {$this->newPlayer}");

                        // player_shop_items
                        $player_shop_items = $this->base(self::FIRST_BASE)->select('player_shop_items', [], "`shop_id` = :shop", ['shop' => $ps['id']]);
                        foreach ($player_shop_items as $psi){
                            $column = ['vnum', 'count', 'pos', 'display_pos', 'price', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6', 'applytype0', 'applyvalue0', 'applytype1', 'applyvalue1', 'applytype2', 'applyvalue2', 'applytype3', 'applyvalue3', 'applytype4', 'applyvalue4', 'applytype5', 'applyvalue5', 'applytype6', 'applyvalue6', 'applytype7', 'applyvalue7'];
                            $column = array_combine($column, array_map(function ($v) use ($psi) { return $psi[$v]; }, $column));
                            $this->base(self::SECOND_BASE)->insert('player_shop_items', array_merge([
                                'shop_id'   => $shop,
                                'player_id' => $this->newPlayer
                            ], $column));

                            $this->text("[player_shop_items] player {$this->newPlayer}");
                        }
                    }

                    // quest
                    $quest = $this->base(self::FIRST_BASE)->select('quest', [], "`dwPID` = :player", ['player' => $this->lastPlayer]);
                    foreach ($quest as $q){
                        $this->base(self::SECOND_BASE)->insert('quest', [
                            'dwPID'     => $this->newPlayer,
                            'szName'    => $q['szName'],
                            'szState'   => $q['szState'],
                            'lValue'    => $q['lValue'],
                        ]);

                        $this->text("[quest] player {$this->newPlayer}");
                    }


                }   // END LOOP PLAYER

                // player index
                $pi = $this->base(self::FIRST_BASE)->select('player_index', [], "`id` = :account", ['account' => $this->lastAccount]);
                if(isset($pi[0])){
                    $pi = $pi[0];
                    $this->base(self::SECOND_BASE)->insert('player_index', [
                        'id'    => $this->newAccount,
                        'pid1'  => $player_index['0'] ?? 0,
                        'pid2'  => $player_index['1'] ?? 0,
                        'pid3'  => $player_index['2'] ?? 0,
                        'pid4'  => $player_index['3'] ?? 0,
                        'pid5'  => $player_index['4'] ?? 0,
                        'empire'=> $pi['empire'] ?? 0,
                    ]);
                }

                // safebox
                $safebox = $this->base(self::FIRST_BASE)->select('safebox', [], "`account_id` = :account", ['account' => $this->lastAccount]);
                foreach ($safebox as $s){
                    $this->base(self::SECOND_BASE)->insert('safebox', [
                        'account_id'    => $this->newAccount,
                        'size'          => $s['size'],
                        'password'      => $s['password'],
                        'gold'          => $s['gold'],
                    ]);

                    $this->text("[safebox] account {$this->newAccount}");
                }

            }   // END LOOP ACCOUNT
        }

        // marriage
        $marriage = $this->base(self::FIRST_BASE)->select('marriage');
        foreach ($marriage as $m){
            $this->base(self::SECOND_BASE)->insert('marriage', [
                'is_married'    => $m['is_married'],
                'pid1'          => $this->players[$m['pid1']],
                'pid2'          => $this->players[$m['pid2']],
                'love_point'    => $m['love_point'],
                'time'          => $m['time'],
            ]);
        }

        // messenger_list
        $messenger = $this->base(self::FIRST_BASE)->select('messenger_list');
        foreach ($messenger as $m){
            $this->base(self::SECOND_BASE)->insert('messenger_list', [
                'account'       => $m['account'].$this->duplicat,
                'companion'     => $m['companion'].$this->duplicat,
            ]);
        }

        // guild
        $guild = $this->base(self::FIRST_BASE)->select('guild');
        foreach ($guild as $g){
            $column = ['name', 'sp', 'level', 'exp', 'skill_point', 'skill', 'win', 'draw', 'loss', 'ladder_point', 'gold', 'dungeon_ch', 'dungeon_map', 'dungeon_cooldown', 'dungeon_start'];
            $column = array_combine($column, array_map(function ($v) use ($g) { return $g[$v]; }, $column));
            $guild = $this->base(self::SECOND_BASE)->insert('guild', array_merge([
                'master' => $this->players[$g['master']]
            ], $column));

            $guild_grade = $this->base(self::FIRST_BASE)->select('guild_grade', [], "`guild_id` = :guild", ['guild' => $g['id']]);
            foreach ($guild_grade as $gg){
                $this->base(self::SECOND_BASE)->insert('guild_grade', [
                    'guild_id' => $guild,
                    'grade' => $gg['grade'],
                    'name'  => $gg['name'],
                    'auth'  => $gg['auth'],
                ]);
            }

            $guild_member = $this->base(self::FIRST_BASE)->select('guild_member', [], "`guild_id` = :guild", ['guild' => $g['id']]);
            foreach ($guild_member as $gg){
                $this->base(self::SECOND_BASE)->insert('guild_member', [
                    'pid'           => $this->players[$gg['pid']],
                    'guild_id'      => $guild,
                    'grade'         => $gg['grade'],
                    'is_general'    => $gg['is_general'],
                    'offer'         => $gg['offer'],
                ]);
            }

            $this->text("[guild] $guild player {$this->newPlayer}");
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
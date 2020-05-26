# M2-System-Merge

The system is used to connect two game servers into one. Moves items, account, player without overwriting the ID and not losing items.   Everything is correctly assigned.  
Example:  
We have the first server with 1000 account, 2000 player, 10,000 items.  
We have a second server with 500 account, 1000 player and 3000 items.  
The system combines everything in:  
1500 account, 3000 player, 13000 items without losing anything. All IDs are correctly assigned, all items are correctly assigned to new users.  
The system is fully automatic, you can easily add new tables and columns. (It's a generator)  

I recommend PHP version min. 7.4

USE THIS AS A CLI EXAMPLE: php cli.php  
Remember to set the character encoding  
May two databases have to have the same file structures  
keep account, player and other tables in one database  
Make a copy of the data, do not operate live. It's best to create two new bases and upload two servers there. The script says "secondBase" so be careful. MAKE A BACKUP COPY  
 
Support for
 ```
 affect
 guild
 item
 player
 player_index
 player_gift (option system)
 player_shop_items (option system)
 quest
 safebox
 ```
Not supported
 ```
 guild_grade
 guild_member
 marriage
 messenger_list
 ```
 
Example:
 ```
 $migration = new Migration;

// Coding Type
$migration->charset = 'gb2312_chinese_ci';

// Table settings
$migration->account('account', 'id', ['login', 'password', 'social_id', 'email', 'securitycode', 'status', 'availDt', 'create_time', 'last_play', 'gold_expire', 'silver_expire', 'safebox_expire', 'autoloot_expire', 'fish_mind_expire', 'marriage_fast_expire', 'money_drop_rate_expire', 'real_name', 'question1', 'answer1', 'question2', 'answer2', 'cash']);
$migration->player('player', 'id', 'account', ['name', 'job', 'kingdom', 'voice', 'dir', 'x', 'y', 'z', 'map_index', 'exit_x', 'exit_y', 'exit_map_index', 'hp', 'mp', 'stamina', 'random_hp', 'random_sp', 'playtime', 'level', 'level_step', 'st', 'ht', 'dx', 'iq', 'exp', 'gold', 'stat_point', 'skill_point', 'quickslot', 'ip', 'part_main', 'part_base', 'part_hair', 'part_acce', 'skill_group', 'skill_level', 'alignment', 'last_play', 'change_name', 'mobile', 'sub_skill_point', 'stat_reset_count', 'horse_hp', 'horse_stamina', 'horse_level', 'horse_hp_droptime', 'horse_riding', 'horse_skill_point']);

// Database connection settings
$migration->firstBase('localhost', 'user', 'pass', 'migration_1');
$migration->secondBase('localhost', 'user', 'pass', 'migration_2');

// List of tables to migrate
// The account and player table is automatically added
$migration->add('affect', 'dwPID', ['bType', 'bApplyOn', 'lApplyValue', 'dwFlag', 'lDuration', 'lSPCost']);
$migration->add('guild', 'master', ['name', 'sp', 'master', 'level', 'exp', 'skill_point', 'skill', 'win', 'draw', 'loss', 'ladder_point', 'gold', 'dungeon_ch', 'dungeon_map', 'dungeon_cooldown', 'dungeon_start']);
$migration->add('item', 'owner_id', ['window', 'pos', 'count', 'vnum', 'bind', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6']);
$migration->add('player_gift', 'owner_id', ['date_add', 'date_get', 'status', 'from', 'reason', 'vnum', 'count', 'socket0', 'socket1', 'socket2', 'socket3', 'socket4', 'socket5', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6', 'applytype0', 'applyvalue0', 'applytype1', 'applyvalue1', 'applytype2', 'applyvalue2', 'applytype3', 'applyvalue3', 'applytype4', 'applyvalue4', 'applytype5', 'applyvalue5', 'applytype6', 'applyvalue6', 'applytype7', 'applyvalue7']);
$migration->add('player_shop', 'player_id', ['shop_vid', 'item_count', 'name', 'status', 'map_index', 'x', 'y', 'z', 'date', 'date_close', 'ip', 'gold', 'cash', 'channel', 'npc', 'npc_decoration']);
$migration->add('quest', 'dwPID', ['szName', 'szState', 'lValue']);


// Migration Process
$migration->start();
 ```
 
Add table
 ```
 $migration->add('table name', 'player index', ['array column table']);
 ```
 The system does have a positive side effect. Cleans inanimate objects. (If the user deletes the character, the game engine does not delete these items)

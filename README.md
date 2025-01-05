# Trampoline Plugin for PocketMine-MP

**Trampoline** is a customizable PocketMine-MP plugin that allows players to create trampolines in their worlds. Players can be launched into the air when they walk over blocks configured as trampolines. The plugin includes features such as adjustable launch power, particle effects, and multi-world support.

---

## Features

- **Customizable trampolines**: Players can set specific blocks as trampolines.
- **Adjustable launch power**: Configure how high players are launched when stepping on a trampoline.
- **Slime-like particle effects**: Enable or disable particles when players jump on trampolines.
- **Multi-world support**: Create trampolines in different worlds.
- **Commands**: Set, remove, and list trampolines using simple commands.
- **Configuration files**: Customize messages, launch power, and particles through config files.

---

## Commands

| Command                       | Description                               | Usage Example                        |
|-------------------------------|-------------------------------------------|--------------------------------------|
| `/trampoline set <count>`     | Enter set mode to create a trampoline.    | `/trampoline set 4`                  |
| `/trampoline remove <ID>`     | Remove a trampoline by its ID.            | `/trampoline remove 1`               |
| `/trampoline edit <ID>`       | Edit trampoline settings by ID.           | `/trampoline edit 1`                 |
| `/trampoline list`            | List all created trampolines.             | `/trampoline list`                   |
| `/trampoline cancel`          | Cancel the current set mode.              | `/trampoline cancel`                 |

---

## Permissions

| Permission          | Description                               | Default |
|---------------------|-------------------------------------------|---------|
| `trampoline.cmd`    | Allows use of all trampoline commands.    | `op`  |

---

## Configuration Files

### `config.yml`

This file allows you to customize the global settings for the plugin.

```yaml
# The throw power applied to players when they step on the trampoline
throwPower: 2.0

# Enable or disable slime particles when the player jumps
enableSlimeParticles: true
```

---

### `lang.yml`

This file allows you to customize all the messages displayed by the plugin.

```yaml
prefix: "§a[Trampoline] §r"
no-permission: "§cYou don't have permission to use this command."
only-in-game: "§cThis command can only be used in-game."
usage: "§cUsage: /trampoline <set|remove|edit|list> [params]"
set-start: "You are now in set mode. Break %count% block(s) to define your trampoline."
set-done: "Trampoline #%id% has been created with %count% block(s)."
exit-set-mode: "You are no longer in set mode."
already-in-set-mode: "You are already in set mode. Use /trampoline cancel to exit."
no-trampolines: "There are no trampolines created yet."
list-format: "§eTrampoline §f#%id%§e has §f%count% block(s)§e in world: §f%world%."
remove-success: "Trampoline #%id% was successfully removed."
remove-fail: "No trampoline found with ID: #%id%."
edit-success: "Trampoline #%id% has been updated."
edit-fail: "Failed to edit. Make sure the ID is correct and the parameters are valid."
cancel-success: "Set mode canceled."
```

---

### `trampolines.yml`

This file stores all the trampolines created by players. It is automatically managed by the plugin and should not be edited manually unless necessary.

Example:

```yaml
trampolines:
  1:
    blocks:
      - world: "world"
        x: 100
        y: 65
        z: 200
      - world: "world"
        x: 101
        y: 65
        z: 200
```

---

## How to Use

1. **Set a trampoline**:  
   Use `/trampoline set <count>` to enter set mode. Once in set mode, break the specified number of blocks you want to act as trampolines.  
   Example:  
   ```bash
   /trampoline set 4
   ```
   Then, break 4 blocks. These blocks will now function as trampolines.

2. **Remove a trampoline**:  
   Use `/trampoline remove <ID>` to remove a trampoline by its ID.  
   Example:  
   ```bash
   /trampoline remove 1
   ```

3. **Edit a trampoline**:  
   Use `/trampoline edit <ID>` to modify a trampoline’s settings.

4. **List trampolines**:  
   Use `/trampoline list` to see all the trampolines that have been created.

5. **Cancel set mode**:  
   Use `/trampoline cancel` to exit set mode without creating a trampoline.

---

## Installation

1. Download the plugin (`Trampoline.zip`) and extract it.
2. Place the extracted `Trampoline` folder inside your `plugins` directory.
3. Start or restart your PocketMine-MP server.
4. Modify the configuration files (`config.yml`, `lang.yml`) to your liking.
5. Use the commands to create and manage trampolines in your world.

---

## Requirements

- **PocketMine-MP**: Version 5.0.0 or higher
- No external dependencies are required.

---

## Support

If you encounter any issues or have feature requests, feel free to contact the author (**SoyDavs**) or open an issue on the plugin’s repository.

---

## License

This plugin is provided under the **MIT License**. You are free to use, modify, and distribute it as long as proper credit is given.


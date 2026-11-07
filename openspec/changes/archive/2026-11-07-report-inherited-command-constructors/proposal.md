# Proposal

## Why

P-DI-04 looked only at a constructor written in the command's own class. Of the 42 commands registered in the 56 modules checked, 3 have no constructor of their own, and 2 of those inherit one with class parameters, so their dependencies went unchecked. Magento creates them all the same.

## What Changes

- **P-DI-04:** a command's constructor is read from reflection, so an inherited one counts. A finding stays on the constructor when the class has its own, and is on the class otherwise.
- **`Filesystem`:** stays excluded, with the real reason. Among the commands in `CommandList` only those of sample data take it, and its constructor just stores three cheap arguments.

## Impact

The default set does not change: P-DI-04 is off by default.

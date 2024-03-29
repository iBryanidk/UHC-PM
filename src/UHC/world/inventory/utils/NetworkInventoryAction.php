<?php

namespace UHC\world\inventory\utils;

final class NetworkInventoryAction {

    const SOURCE_CONTAINER = 0;

    const SOURCE_WORLD = 2;
    const SOURCE_CREATIVE = 3;
    const SOURCE_TODO = 99999;
    const SOURCE_CRAFT_SLOT = 100;

    const SOURCE_TYPE_CRAFTING_ADD_INGREDIENT = -2;
    const SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT = -3;
    const SOURCE_TYPE_CRAFTING_RESULT = -4;
    const SOURCE_TYPE_CRAFTING_USE_INGREDIENT = -5;

    const SOURCE_TYPE_ANVIL_INPUT = -10;
    const SOURCE_TYPE_ANVIL_MATERIAL = -11;
    const SOURCE_TYPE_ANVIL_RESULT = -12;
    const SOURCE_TYPE_ANVIL_OUTPUT = -13;

    const SOURCE_TYPE_ENCHANT_INPUT = -15;
    const SOURCE_TYPE_ENCHANT_MATERIAL = -16;
    const SOURCE_TYPE_ENCHANT_OUTPUT = -17;

    const SOURCE_TYPE_TRADING_INPUT_1 = -20;
    const SOURCE_TYPE_TRADING_INPUT_2 = -21;
    const SOURCE_TYPE_TRADING_USE_INPUTS = -22;
    const SOURCE_TYPE_TRADING_OUTPUT = -23;

    const SOURCE_TYPE_BEACON = -24;

    const SOURCE_TYPE_CONTAINER_DROP_CONTENTS = -100;

}

?>
<?php
$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true); // Le deuxième argument permet de retourner un tableau associatif
foreach ($allProducts as $key => $value) {
    $allProducts[$key] = removeLastUnderscoreAndChar($value);
}
$allProducts = array_unique($allProducts);


function createObject($class, $data)
{
    $url = "http://localhost/api/index.php/" . $class;
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: Z35STg78bC2kPB8AIXke4rof3MlXqj17' // clef à changer
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function createNewProduct($name, $label)
{
    $data = array(
        'ref' => $name,
        'label' => $label,
        'price' => createPrice($name),
        'status' => 1,
        'status_buy' => 1
    );
    createObject("products", $data);
}

function initAllProducts($productList)
{
    $dataTranslate = file_get_contents('productsEN-FR.json');
    $allProductsTranslate = json_decode($dataTranslate, true); // Le deuxième argument permet de retourner un tableau associatif
    foreach ($productList as $entry) {
        createNewProduct($entry, frenchName($entry, $allProductsTranslate));
        echo "product: " . $entry . " has been created\n";
    }
}

function separateWords($productList)
{
    $words = array(); // Initialisez le tableau en dehors de la boucle

    foreach ($productList as $entry) {
        $words_temp = explode(":", $entry); // Stockez le résultat dans un tableau temporaire

        $words_after_colon = $words_temp[1];
        $words_after_colon_without_ = str_replace('_', ' ', $words_after_colon);

        // Vérifiez si le mot existe déjà dans le tableau
        if (!in_array($words_after_colon_without_, $words)) {
            $words[] = $words_after_colon_without_; // Ajoutez le mot au tableau
        }
    }

    return $words;
}

function separateWord($product)
{
    $words_temp = explode(":", $product); // Stockez le résultat dans un tableau temporaire

    $words_after_colon = $words_temp[1];
    $words_after_colon_without_ = str_replace('_', ' ', $words_after_colon);

    return $words_after_colon_without_;
}


function frenchName($searchString, $allProductsTranslate)
{
    $separateWord = separateWord($searchString);
    foreach ($allProductsTranslate as $item) {
    if (isset($item['english']) && $item['english'] === $separateWord) {
            $englishName = $item['english'];
            break; // La chaîne a été trouvée, pas besoin de continuer la recherche
        }
    }

    if (!empty($englishName)) {
        // echo "Le nom en anglais de '$searchString' est : $englishName\n";
        return $item['french'];
    } else {
        echo "La chaîne '$searchString' n'a pas été trouvée dans le tableau.\n";
    }
}

function removeLastUnderscoreAndChar($input)
{
    return preg_replace('/_[a-zA-Z0-9]$/', '', $input);
}

function createPrice($name)
{ // fonction qui retourne le prix d'un item en fonction de son nom

    /*
     *  lors de craft certain prix on un +1 supplémentaire du prix total des objets craft surtout les outils comme bénéfice au craft
     */

    $price = -1; // debug du prix si probleme dans les items craft // exemple -> axe dirt

// items fait de minerai

    if (strpos($name, "pick") !== false || strpos($name, "axe") !== false) {
        if (strpos($name, "wood") !== false) {
            $price = 3 + 1 + 1;
        } elseif (strpos($name, "stone") !== false) {
            $price = 6 + 1 + 1;
        } elseif (strpos($name, "bronze") !== false) {
            $price = 18 + 1 + 1;
        } elseif (strpos($name, "steel") !== false) {
            $price = 30 + 1 + 1;
        } elseif (strpos($name, "mese") !== false) {
            $price = 300 + 1 + 1;
        } elseif (strpos($name, "diamond") !== false) {
            $price = 60 + 1 + 1;
        }
        return $price;
    }


    if (strpos($name, "sword") !== false) {
        if (strpos($name, "wood") !== false) {
            $price = 2 + 1 + 1;
        } elseif (strpos($name, "stone") !== false) {
            $price = 4 + 0.5 + 1;
        } elseif (strpos($name, "bronze") !== false) {
            $price = 12 + 0.5 + 1;
        } elseif (strpos($name, "steel") !== false) {
            $price = 20 + 0.5 + 1;
        } elseif (strpos($name, "mese") !== false) {
            $price = 200 + 0.5 + 1;
        } elseif (strpos($name, "diamond") !== false) {
            $price = 40 + 0.5 + 1;
        }
        return $price;
    }


    if (strpos($name, "hoe") !== false) {
        if (strpos($name, "wood") !== false) {
            $price = 2 + 1 + 1;
        } elseif (strpos($name, "stone") !== false) {
            $price = 4 + 1 + 1;
        } elseif (strpos($name, "steel") !== false) {
            $price = 20 + 1 + 1;
        }
        return $price;
    }


    if (strpos($name, "shovel") !== false) {
        if (strpos($name, "wood") !== false) {
            $price = 1 + 1 + 1;
        } elseif (strpos($name, "stone") !== false) {
            $price = 2 + 1 + 1;
        } elseif (strpos($name, "bronze") !== false) {
            $price = 6 + 1 + 1;
        } elseif (strpos($name, "steel") !== false) {
            $price = 10 + 1 + 1;
        } elseif (strpos($name, "mese") !== false) {
            $price = 100 + 1 + 1;
        } elseif (strpos($name, "diamond") !== false) {
            $price = 20 + 1 + 1;
        }
        return $price;
    }


// porte , trapdoor,  mur , ...

    if (strpos($name, "trapdoor") !== false) {
        if (strpos($name, "steel bar") !== false) {
            $price = 12 + 1;
        } elseif (strpos($name, "steel") !== false) {
            $price = 41;
        } else {
            $price = 6 + 1;
        }
        return $price;
    }


    if (strpos($name, "door") !== false) {
        if (strpos($name, "steel bar") !== false) {
            $price = 19;
        } elseif (strpos($name, "steel") !== false) {
            $price = 61;
        } elseif (strpos($name, "obsidian") !== false) {

            $price = 6 + 1;
        } elseif (strpos($name, "glass") !== false) {
            $price = 6 + 1;
        } else {
            $price = 6 + 1;
        }
        return $price;
    }

    if (strpos($name, "fence rail") !== false) {
        $price = 0.25;
        return $price;
    }

    if (strpos($name, "fence") !== false) {
        $price = 1.25;
        return $price;
    }

    if (strpos($name, "gate") !== false) {
        $price = 6 + 1;
        return $price;
    }

    if (strpos($name, "walls") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "bar flat") !== false) {
        $price = 3;
        return $price;
    }

    if (strpos($name, "pane flat") !== false) {
        $price = 0.4;
        return $price;
    }


// block et item  basique


    if (strpos($name, "stick") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "stone") !== false) {
        $price = 2;
        return $price;
    }


    if (strpos($name, "coal lump") !== false) {
        $price = 3;
        return $price;
    }

    if (strpos($name, "copper lump") !== false) {
        $price = 4;
        return $price;
    }

    if (strpos($name, "clay lump") !== false) {
        $price = 2;
        return $price;
    }


    if (strpos($name, "iron lump") !== false) {
        $price = 5;
        return $price;
    }

    if (strpos($name, "tin lump") !== false) {
        $price = 6;
        return $price;
    }

    if (strpos($name, "gold lumb") !== false) {
        $price = 8;
        return $price;
    }

    if (strpos($name, "mese crystal fragment") !== false) {
        $price = 11;
        return $price;
    }


    if (strpos($name, "mese crystal") !== false) {
        $price = 100;
        return $price;
    }

    if (strpos($name, "clay brick") !== false) {
        $price = 2;
        return $price;
    }


    if (strpos($name, "flint and steel") !== false) {
        $price = 12;
        return $price;
    }

    if (strpos($name, "steel") !== false) {
        $price = 10;
        return $price;
    }

    if (strpos($name, "copper") !== false) {
        $price = 5;
        return $price;
    }

    if (strpos($name, "tin") !== false) {
        $price = 7;
        return $price;
    }

    if (strpos($name, "bronze") !== false) {
        $price = 6;
        return $price;
    }

    if (strpos($name, "gold") !== false) {
        $price = 12;
        return $price;
    }

    if (strpos($name, "diamond") !== false) {
        $price = 20;
        return $price;
    }


    if (strpos($name, "mese") !== false) {
        $price = 900;
        return $price;
    }


// items particuliers


    if (strpos($name, "paper") !== false) {
        $price = 3;
        return $price;
    }

    if (strpos($name, "book") !== false) {
        $price = 3;
        return $price;
    }

    if (strpos($name, "bookshelf") !== false) {
        $price = 15 + 1;
        return $price;
    }

    if (strpos($name, "bucket") !== false) {
        $price = 30 + 1;
        return $price;
    }

    if (strpos($name, "screwdriver") !== false) {
        $price = 12;
        return $price;
    }

    if (strpos($name, "torch") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "key") !== false) {
        $price = 8;
        return $price;
    }

    if (strpos($name, "binoculars") !== false) {
        $price = 4 + 16 + 1;
        return $price;
    }

    if (strpos($name, "mapping kit") !== false) {
        $price = 27;
        return $price;
    }

    if (strpos($name, "bug net") !== false) {
        $price = 5 + 1;
        return $price;
    }

    if (strpos($name, "boat") !== false) {
        $price = 5 + 1;
        return $price;
    }

    if (strpos($name, "cart") !== false) {
        $price = 50 + 1;
        return $price;
    }


    if (strpos($name, "gunpowder") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "tnt stick") !== false) {
        $price = 4;
        return $price;
    }

    if (strpos($name, "tnt") !== false) {
        $price = 30;
        return $price;
    }


    if (strpos($name, "obsidian shard") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "obsidian glass") !== false) {
        $price = 1;
        return $price;
    }

    if (strpos($name, "obsidian") !== false) {
        $price = 9;
        return $price;
    }


    $price = 1;
    return $price;

}

initAllProducts($allProducts);
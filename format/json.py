bad_json:str = "{'NoProduit': 2, 'Nom': 'miel', 'NoProducteur': 1, 'PrixKilo': 6.0, 'Bio': 1, 'ImagePath': '/assets/images/miel.webp'}, {'NoProduit': 3, 'Nom': 'baguette', 'NoProducteur': 2, 'PrixKilo': 1.0, 'Bio': 0, 'ImagePath': '/assets/images/baguette.webp'}, {'NoProduit': 4, 'Nom': 'pomme', 'NoProducteur': 3, 'PrixKilo': 2.0, 'Bio': 1, 'ImagePath': '/assets/images/pomme.webp'}, {'NoProduit': 5, 'Nom': 'baignet', 'NoProducteur': 2, 'PrixKilo': 10.0, 'Bio': 0, 'ImagePath': '/assets/images/baignet.webp'}, {'NoProduit': 6, 'Nom': 'patate', 'NoProducteur': 1, 'PrixKilo': 3.0, 'Bio': 1, 'ImagePath': '/assets/images/patate.webp'}, {'NoProduit': 7, 'Nom': 'poireaux', 'NoProducteur': 1, 'PrixKilo': 6.0, 'Bio': 1, 'ImagePath': '/assets/images/poireaux.webp'}, {'NoProduit': 8, 'Nom': 'chocolatine', 'NoProducteur': 2, 'PrixKilo': 4.0, 'Bio': 1, 'ImagePath': '/assets/images/chocolatine.webp'}, {'NoProduit': 9, 'Nom': 'croissant', 'NoProducteur': 2, 'PrixKilo': 3.0, 'Bio': 0, 'ImagePath': '/assets/images/croissant.webp'}, {'NoProduit': 10, 'Nom': 'confiture_fraise', 'NoProducteur': 3, 'PrixKilo': 3.0, 'Bio': 0, 'ImagePath': '/assets/images/confiture_fraise.webp'}"

def fix_json(bad_json: str) -> str:
    """
    Fixes a JSON string by replacing single quotes with double quotes.

    Args:
        bad_json (str): The JSON string to fix.

    Returns:
        str: The fixed JSON string.
    """
    for i in range(len(bad_json)):
        if bad_json[i] == "'":
            bad_json = bad_json[:i] + '"' + bad_json[i+1:]

    return bad_json

print(fix_json(bad_json))
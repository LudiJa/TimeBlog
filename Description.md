# Projet de cours : WF Camp

## Thème du projet : Microblogging (comprendre twitter par ex.)

Mettre en place un système d'authentification des utilisateurs via une interface sécurisée.

### Fonctionnalités :

    - Poster des billets/posts de 400 caractères maximum.
    - Affichage dans une liste antéchronologique pour les posts.
    - Réagir aux posts des autres utilisateurs (via un système de commentaires).
    - Créer un panel administrateur.
    - Ces posts pourront inclure des fichiers média (jpg,png,gif).
    - Les utilisateurs pourront "liker" les posts des utilisateurs. (Ainsi que les leurs, à priori on va l'activer automatiquement).
    - Système de tags (?)
    - Créer des liens avec des utilisateurs (?)
    - Créer un système plus fluide grâce à l'AJAX (???)

### Logique Métier du projet :

    - CRUD sur les posts (soumis à autorisation)
    - Authentification & Page de profil (qui permet d'afficher les infos/posts de l'utilisateur)
    - Système de rôles d'utilisateurs (Utilisateurs & Admin)
    - Les admins ont accès à un panel administrateur
    - Upload de fichiers
    - Like sur les posts

### MLD

    ```json
    User {
        "id":"int pk",
        "username" : "string, 21, unique",
        "email": "email, unique",
        "password": "password",
        "roles": "json",
        "birthdate": "date",
        "createdAt": "date",
        "avatar" : "string",
        "posts" : "relation",
        "likes" : "relation",
    }
    ```

    ```json
    Post{
        "id":"int pk",
        "content": "text,400",
        "media": "string",
        "user":"relation w/ User",
        "likes" : "relation w/ Users",
        "comments" : "relation w/ Comments",
        "createdAt" : "datetime",
        "updatedAt" : "datetime",
    }
    ```

    ```json
    Comment {
        "id":"int pk",
        "content": "text,300",
        "author":"relation w/ User",
        "post": "relation w/ Post",
        "createdAt" : "datetime",
        "updatedAt" : "datetime",
    }
    ```

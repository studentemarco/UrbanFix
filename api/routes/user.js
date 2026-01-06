// routes/user.js

const express = require('express');
const router = express.Router();
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

// Middleware di autenticazione Bearer
function authMiddleware(req, res, next) {
    const Authorization = req.headers['authorization'];
    if (!Authorization || !Authorization.startsWith('Bearer ')) {
        return res.status(401).json({ error: 'Unauthorized: Missing or invalid Authorization header.' });
    }
    const token = Authorization.split(' ')[1];
    if (token !== '5IDtoken') {
        return res.status(403).json({ error: 'Forbidden: Invalid token.' });
    }
    next();
}
/** 
 * @swagger
 * security:
 *   - apiKey: []
 *
 * components:
 *   securitySchemes:
 *     apiKey:
 *       type: apiKey
 *       name: Authorization
 *       in: header
 */
/**
 * @swagger
 * /problemi:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera la lista dei problemi attivi dal database MariaDB
 *     responses:
 *       200:
 *         description: Lista dei problemi caricata con successo
 *       500:
 *         description: Errore del server database
 */

router.get('/problemi', (req, res) => {

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_Problemi
            where stato != 'risolto'
                AND ID NOT IN (SELECT Problemi_ID FROM UrbanFix_EliminaP)
            order by timestampSegnalazione desc`;

    connection.query(q, (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        problemi = results;
        res.status(200).json(problemi);
    });

    connection.end();
});

/**
 * @swagger
 * /problemi/{id}:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera i dettagli di un singolo problema attivo tramite ID
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: L'ID del problema da recuperare
 *     responses:
 *       200:
 *         description: Dettagli del problema recuperati con successo
 *       404:
 *         description: Problema non trovato o eliminato
 *       500:
 *         description: Errore del server database
 */

router.get('/problemi/:id', (req, res) => {

    const idProblema = req.params.id;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_Problemi
            where id = ?
                AND ID NOT IN (SELECT Problemi_ID FROM UrbanFix_EliminaP)
            order by timestampSegnalazione desc`;   // il ? serve per evitare SQL Injection, il valore della variabile viene passato come parametro a connection.query

    connection.query(q, [idProblema], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Problema non trovato.' });
        } else{
            problemi = results;
        }
        res.status(200).json(problemi);
    });

    connection.end();
});

/**
 * @swagger
 * /problemi/comune/{qid}:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera la lista dei problemi attivi per Comune (QID)
 *     parameters:
 *       - in: path
 *         name: qid
 *         required: true
 *         schema:
 *           type: string
 *         description: Il QID del comune (es. Q220)
 *     responses:
 *       200:
 *         description: Lista dei problemi recuperata con successo
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   ID:
 *                     type: integer
 *                     example: 22
 *                   descrizione:
 *                     type: string
 *                     example: "Buche profonde in via Roma"
 *                   coordinate:
 *                     type: string
 *                     example: "41.9028,12.4964"
 *                   stato:
 *                     type: string
 *                     example: "aperto"
 *                   timestampStato:
 *                     type: string
 *                     format: date-time
 *                     example: "2026-01-04T14:06:07.000Z"
 *                   timestampSegnalazione:
 *                     type: string
 *                     format: date-time
 *                     example: "2026-01-04T14:06:07.000Z"
 *                   Comuni_QID:
 *                     type: string
 *                     example: "Q220"
 *                   Utenti_email:
 *                     type: string
 *                     example: "anna.bianchi@mail.it"
 *       404:
 *         description: Nessun problema trovato per il comune specificato
 *       500:
 *         description: Errore del server database
 */


router.get('/problemi/comune/:qid', (req, res) => {

    const qidProblema = req.params.qid;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_Problemi
            where stato != 'risolto' and Comuni_QID = ?
            order by timestampSegnalazione desc;`;

    connection.query(q, [qidProblema], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Nessun problema trovato per il comune specificato.' });
        } else{
            problemi = results;
        }
        res.status(200).json(problemi);
    });

    connection.end();
});


/**
 * @swagger
 * /problemi/utente/{email}:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera la lista dei problemi segnalati da uno specifico utente
 *     parameters:
 *       - in: path
 *         name: email
 *         required: true
 *         schema:
 *           type: string
 *           format: email
 *         description: L'indirizzo email dell'utente (es. anna.bianchi@mail.it)
 *     responses:
 *       200:
 *         description: Lista dei problemi dell'utente recuperata con successo
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   ID:
 *                     type: integer
 *                     example: 22
 *                   descrizione:
 *                     type: string
 *                     example: "Buche profonde in via Roma"
 *                   coordinate:
 *                     type: string
 *                     example: "41.9028,12.4964"
 *                   stato:
 *                     type: string
 *                     example: "aperto"
 *                   timestampStato:
 *                     type: string
 *                     format: date-time
 *                     example: "2026-01-04T14:06:07.000Z"
 *                   timestampSegnalazione:
 *                     type: string
 *                     format: date-time
 *                     example: "2026-01-04T14:06:07.000Z"
 *                   Comuni_QID:
 *                     type: string
 *                     example: "Q220"
 *                   Utenti_email:
 *                     type: string
 *                     example: "anna.bianchi@mail.it"
 *       404:
 *         description: Nessun problema trovato per l'utente specificato
 *       500:
 *         description: Errore del server database
 */

router.get('/problemi/utente/:email', (req, res) => {

    const emailUtente = req.params.email;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_Problemi
            where Utenti_Email = ?
            order by timestampSegnalazione desc;`;

    connection.query(q, [emailUtente], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Nessun problema trovato per l\'utente specificato.' });
        } else{
            problemi = results;
        }
        res.status(200).json(problemi);
    });

    connection.end();
});

/**
 * @swagger
 * /problemi/{id}/commenti:
 *   get:
 *     tags:
 *       - Commenti
 *     summary: Recupera i commenti di un problema
 *     description: Restituisce la lista dei commenti associati a un ID problema, escludendo quelli presenti nella tabella UrbanFix_EliminaC.
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: L'ID univoco del problema (es. 10)
 *     responses:
 *       200:
 *         description: Lista dei commenti recuperata con successo
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   ID:
 *                     type: integer
 *                     example: 101
 *                   Problemi_ID:
 *                     type: integer
 *                     example: 10
 *                   testo:
 *                     type: string
 *                     example: "Hanno transennato l'area proprio ora."
 *                   time:
 *                     type: string
 *                     format: date-time
 *                     example: "2026-01-06T17:15:00.000Z"
 *                   Utenti_email:
 *                     type: string
 *                     example: "mario.rossi@mail.it"
 *       404:
 *         description: Nessun commento trovato per il problema specificato
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Nessun commento trovato per il problema specificato."
 *       500:
 *         description: Errore del server database
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Errore del server durante il recupero dei dati."
 */

router.get('/problemi/:id/commenti', (req, res) => {

    const idProblema = req.params.id;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_Commenti
            where Problemi_ID = ?
                AND ID NOT IN (SELECT Commenti_ID FROM UrbanFix_EliminaC)
            order by time desc;`;

    connection.query(q, [idProblema], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Nessun commento trovato per il problema specificato.' });
        } else{
            commenti = results;
        }
        res.status(200).json(commenti);
    });

    connection.end();
});

/**
 * @swagger
 * /problemi/{id}/immagini:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera le immagini associate a un problema
 *     description: Restituisce l'elenco delle immagini collegate a un ID problema, verificando che il problema non sia stato eliminato.
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: L'ID del problema di cui recuperare le immagini
 *     responses:
 *       200:
 *         description: Lista delle immagini recuperata con successo
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   ID:
 *                     type: integer
 *                     example: 50
 *                   URL:
 *                     type: string
 *                     example: "esempio.it"
 *                   Problemi_ID:
 *                     type: integer
 *                     example: 10
 *       404:
 *         description: Nessun immagine trovata o il problema è stato eliminato
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Nessun immagine trovata per il problema specificato."
 *       500:
 *         description: Errore interno del server
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Errore del server durante il recupero dei dati."
 */

router.get('/problemi/:id/immagini', (req, res) => {

    const idProblema = req.params.id;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select * from UrbanFix_ImmaginiP
            where Problemi_ID = ?
                AND Problemi_ID NOT IN (SELECT Problemi_ID FROM UrbanFix_EliminaP);`;

    connection.query(q, [idProblema], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Nessun immagine trovata per il problema specificato.' });
        } else{
            immagini = results;
        }
        res.status(200).json(immagini);
    });

    connection.end();
});

/**
 * @swagger
 * /problemi/{id}/tipologie:
 *   get:
 *     tags:
 *       - Problemi
 *     summary: Recupera le tipologie associate a un problema
 *     description: Restituisce l'elenco delle tipologie collegate a un ID problema.
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: L'ID del problema di cui recuperare le tipologie
 *     responses:
 *       200:
 *         description: Lista delle tipologie recuperata con successo
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   Tipologie_nome:
 *                     type: string
 *                     example: "Buche stradali"
 *       404:
 *         description: Nessuna tipologia trovata per il problema specificato
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Nessuna tipologia trovata per il problema specificato."
 *       500:
 *         description: Errore interno del server
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: "Errore del server durante il recupero dei dati."
 */

router.get('/problemi/:id/tipologie', (req, res) => {

    const idProblema = req.params.id;

    const mysql = require('mysql2');

    const connection = mysql.createConnection({
        host: 'localhost',
        user: 'utente_phpmyadmin',
        password: 'password_sicura',
        database: 'Database'
    });

    connection.connect();

    let q = `select Tipologie_nome from UrbanFix_Categoria
            where Problemi_ID = ?`;

    connection.query(q, [idProblema], (error, results, fields) => {
        if (error) {
            console.error('Errore durante la query:', error);
            return res.status(500).json({ error: 'Errore del server durante il recupero dei dati.' });
        }
        if (results.length === 0) {
            return res.status(404).json({ error: 'Nessuna tipologia trovata per il problema specificato.' });
        } else{
            tipologie = results;
        }
        res.status(200).json(tipologie);
    });

    connection.end();
});


/**
 * @swagger
 * components:
 *   schemas:
 *     User:
 *       type: object
 *       properties:
 *         name:
 *           type: string
 *           description: The user's name
 *         age:
 *           type: integer
 *           description: The user's age
 */

 /**
  * @swagger
  * /users:
  *   post:
  *     tags:
  *       - Users
  *     security:
  *       - bearerAuth: []
  *     summary: Create a new user
  *     requestBody:
  *       required: true
  *       content:
  *         application/json:
  *           schema:
  *             $ref: '#/components/schemas/User'
  *     responses:
  *       201:
  *         description: User created
  *       400:
  *         description: Bad Request
  *       409:
  *         description: Conflict - User already exists
  */
router.post('/users', authMiddleware, (req, res) => {
    const user = req.body;
    if (!user.hasOwnProperty("name") || !user.hasOwnProperty("age")) {
        return res.status(400).json({ error: 'Bad Request: name and age are required.' });
    }
    const usersPath = path.join(__dirname, '../../users.json');
    fs.readFile(usersPath, 'utf8', (err, data) => {
        let users = [];
        if (!err) {
            try {
                users = JSON.parse(data);
            } catch (parseErr) {
                return res.status(500).json({ error: 'Errore nel parsing del file utenti.' });
            }
        }
        // Check for duplicate names
        if (users.some(u => u.name === user.name)) {
            return res.status(409).json({ error: 'Conflict: User with this name already exists.' });
        }
        users.push(user);
        fs.writeFile(usersPath, JSON.stringify(users, null, 2), (writeErr) => {
            if (writeErr) {
                return res.status(500).json({ error: 'Impossibile salvare il nuovo utente.' });
            }
            // add location header
            res.setHeader('Location', `/api/users/${user.name}`);
            res.status(201).json(user);
        });
    });
});

/**
 * @swagger
 * /users/{name}:
 *   delete:
 *     tags:
 *       - Users
 *     security:
 *       - bearerAuth: []
 *     summary: Elimina un utente per nome
 *     parameters:
 *       - in: path
 *         name: name
 *         required: true
 *         schema:
 *           type: string
 *         description: Nome dell'utente da eliminare
 *     responses:
 *       200:
 *         description: Utente eliminato
 *       404:
 *         description: Utente non trovato
 */
router.delete('/users/:name', authMiddleware, (req, res) => {
    const usersPath = path.join(__dirname, '../../users.json');
    fs.readFile(usersPath, 'utf8', (err, data) => {
        if (err) return res.status(500).json({ error: 'Impossibile leggere il file utenti.' });
        let users = [];
        try {
            users = JSON.parse(data);
        } catch (parseErr) {
            return res.status(500).json({ error: 'Errore nel parsing del file utenti.' });
        }
        const filteredUsers = users.filter(u => u.name !== req.params.name);
        if (filteredUsers.length === users.length) {
            return res.status(404).json({ error: 'Utente non trovato.' });
        }
        fs.writeFile(usersPath, JSON.stringify(filteredUsers, null, 2), (writeErr) => {
            if (writeErr) return res.status(500).json({ error: 'Impossibile eliminare l\'utente.' });
            res.status(200).json({ message: 'Utenti eliminati : ' + (users.length - filteredUsers.length)});
        });
    });
});

/**
 * @swagger
 * /users/{name}:
 *   put:
 *     tags:
 *       - Users
 *     security:
  *       - bearerAuth: []
 *     summary: Aggiorna i dati di un utente per nome
 *     parameters:
 *       - in: path
 *         name: name
 *         required: true
 *         schema:
 *           type: string
 *         description: Nome dell'utente da aggiornare
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/User'
 *     responses:
 *       200:
 *         description: Utente aggiornato
 *       404:
 *         description: Utente non trovato
 */
router.put('/users/:name', authMiddleware, (req, res) => {
    const usersPath = path.join(__dirname, '../../users.json');
    fs.readFile(usersPath, 'utf8', (err, data) => {
        if (err) return res.status(500).json({ error: 'Impossibile leggere il file utenti.' });
        let users = [];
        try {
            users = JSON.parse(data);
        } catch (parseErr) {
            return res.status(500).json({ error: 'Errore nel parsing del file utenti.' });
        }
        const idx = users.findIndex(u => u.name === req.params.name);
        if (idx === -1) {
            return res.status(404).json({ error: 'Utente non trovato.' });
        }
        if (!users[idx].hasOwnProperty("name") || !users[idx].hasOwnProperty("age")) {
            return res.status(400).json({ error: 'Bad Request: name and age are required.' });
        }
        users[idx] = req.body;
        fs.writeFile(usersPath, JSON.stringify(users, null, 2), (writeErr) => {
            if (writeErr) return res.status(500).json({ error: 'Impossibile aggiornare l\'utente.' });
            res.status(200).json(users[idx]);
        });
    });
});

module.exports = router;

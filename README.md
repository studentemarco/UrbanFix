# UrbanFix  
**Piattaforma collaborativa per segnalare problemi sul territorio**

UrbanFix è una piattaforma web interattiva che permette ai cittadini di migliorare il proprio territorio segnalando problemi, commentando, votando e collaborando con il proprio comune.  
I dipendenti comunali e gli amministratori possono gestire le segnalazioni, le tipologie e il territorio di competenza.

---

## Funzionalità principali

### Utente
- Registrazione tramite email (utilizzata come username), con verifica della mail.
- Inserimento di nome, cognome e foto profilo.
- Login, logout e possibilità di reset della password.
- Possibilità di iscriversi a uno o più comuni per ricevere notifiche relative a tali comuni.
- Possibilità di segnalare un problema ovunque sul territorio.
- Inserimento della posizione tramite GPS oppure tramite posizionamento manuale del segnalino sulla mappa.
- Creazione di una segnalazione contenente: testo descrittivo, tipologia, immagini multiple e coordinate geografiche.
- Possibilità di modificare o eliminare una propria segnalazione solo finché non ha ricevuto commenti o voti.
- Possibilità di aggiungere commenti a qualsiasi segnalazione; i commenti possono contenere immagini.
- I commenti possono essere solo eliminati, non modificati.
- Possibilità di votare una segnalazione con un unico tipo di voto (positivo). Il voto può essere aggiunto o rimosso.
- Possibilità di contrassegnare una propria segnalazione come risolta (utile per non renderla ulteriormente visibile ad altri utenti).
- Possibilità di ricevere notifiche riguardo:
  - commenti alle proprie segnalazioni,
  - cambi di stato delle proprie segnalazioni,
  - segnalazioni nei comuni in cui è iscritto (opzionale).

### Comune e gestione territoriale
- Le aree di competenza dei comuni vengono recuperate automaticamente da OpenStreetMap (o altra fonte affidabile).
- Le aree vengono trattate come poligoni; è possibile selezionare più aree nel caso di frazioni o comuni composti da più zone.
- Le segnalazioni sono automaticamente associate al comune in base alla loro posizione geografica sulla mappa.
- Le segnalazioni diventano visibili pubblicamente solo dopo che un numero minimo di utenti ha confermato ("ho anche io questo problema").

### Dipendente comunale
- Accesso a un pannello che visualizza solo le segnalazioni del proprio comune.
- Possibilità di aggiornare lo stato delle segnalazioni attraverso gli stati: nuovo, lavorazione, risolto.
- Possibilità di modificare, eliminare o moderare qualsiasi segnalazione del proprio comune.
- Possibilità di moderare ed eliminare commenti.
- Possibilità di creare nuove tipologie di segnalazioni.
- Possibilità di modificare una tipologia esistente; la modifica si propaga automaticamente a tutte le segnalazioni appartenenti a quella tipologia.
- Possibilità di eliminare una tipologia; le segnalazioni relative vengono assegnate automaticamente alla tipologia "altro".
- Possibilità di modificare parte dei propri dati personali (ad esempio password, e altre informazioni eventualmente consentite).

### Amministratore comunale
- Registrazione alla piattaforma con invio della documentazione necessaria alla verifica.
- L'amministratore della piattaforma approva o rifiuta la richiesta.
- Una volta approvato, l'amministratore comunale può:
  - creare e eliminare account dei dipendenti del proprio comune,
  - assegnare permessi aggiuntivi o speciali ai dipendenti,
  - modificare i dati del comune,
  - selezionare o ridefinire le aree territoriali del comune (se non ottenute automaticamente o se si tratta di territori composti).

### Mappa e consultazione delle segnalazioni
- Visualizzazione di tutte le segnalazioni pubbliche sulla mappa.
- Filtri disponibili:
  - per comune,
  - per regione,
  - per stato della segnalazione,
  - per tipologia,
  - per popolarità,
  - per data.
- Possibilità di ordinare le segnalazioni:
  - per distanza da un punto scelto,
  - per popolarità,
  - per data.

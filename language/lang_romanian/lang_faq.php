<?php
/***************************************************************************
 *                          lang_faq.php [romana fara diacritice]
 *                            -------------------
 *   begin                : Wednesday Aug 7, 2002
 *   copyright 1          : (C) Robert Munteanu
 *   copyright 2          : (C) Bogdan Toma
 *   email     1          : rombert@go.ro
 *   email     2          : bog_tom@yahoo.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
// 
// To add an entry to your FAQ simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your FAQ entries, if you absolutely must then escape them ie. \"something\"
//
// The FAQ items will appear on the FAQ page in the same order they are listed in this file
//
 
  
$faq[] = array("--","Probleme de autentificare si inregistrare");
$faq[] = array("De ce nu ma pot autentifica?", "V-ati inregistrat? Serios vorbind, trebuie sa va inregistrati pentru a va autentifica. Ati fost exclus de pe site (un mesaj va apare pentru a va indica acest lucru) ? Daca da, ar trebui sa contactati adminstratorul site-ului sau forumului pentru a afla motivul. Daca sunteti inregistrat si nu sunteti exclus si tot nu va puteti autentifica, atunci verificati foarte bine numele de utilizator si parola. De obicei, aceasta este problema. Daca nici aceasta nu este solutia, luati legatura cu administratorul forumului pentru ca forumul ar putea fi incorect configurat.");
$faq[] = array("De ce trebuie sa ma inregistrez?", "S-ar putea sa nu fie nevoie, depinde de adminstratorul forumului daca e nevoie sa va inregistrati sau nu. Oricum, inregistrarea va va oferi acces la optiuni care nu sunt disponibile utilizatorilor anonimi cum ar fi imagini asociate, mesaje private, trimiterea de email-uri altor utilizatori, inscrierea in grupuri  etc. Dureaza doar cateva momente sa va inregistrati , asa ca va recomandam sa va inregistrati.");
$faq[] = array("De ce sunt scos afara din forum automat?", "Daca nu activati optiunea <i>Autentifica-ma automat la fiecare vizita</i>, atunci cand va autentificati veti fi autentificat doar pentru o perioada prestabilita. Aceasta masura previne ca cineva sa se foloseasca de contul dumneavoastra. Pentru a ramane autentificat, bifati aceasta optiune la autentificare, dar acest lucru nu este recomandat daca accesati forumul de la un calculator utilizat de mai multe persoane, cum ar fi de la o biblioteca, cafenea internet, facultate etc");
$faq[] = array("Cum fac sa nu imi apara numele de utilizator pe listele de utilizatori conectati?", "In profilul dumneavoastra veti gasi o optiune <i>Ascundeti indicatorul de conectare</i>, si daca veti seta aceasta optiune pe <i>Da</i> veti fi vizibil doar pentru administratori si pentru dumneavoastra. Veti fi numarat ca utilizator ascuns.");
$faq[] = array("Mi-am pierdut parola!", "Nu intrati in panica! Parola dumneavoastra nu poate fi refacuta, dar poate fi resetata. Pentru a realiza acest lucru, mergeti la pagina de autentificare si folositi legatura <i>Mi-am uitat parola</i>, urmati instructiunile si ar trebui sa puteti sa va autentificati in scurt timp.");
$faq[] = array("Sunt inregistrat dar nu ma pot autentifica!", "Mai intai verificati daca ati introdus numele de utilizator si parola corect. Daca sunt corecte, atunci fie, daca este activata optiunea COPPA si ati dat bifat <i>Sunt de acord cu aceste conditii si declar ca am sub 13 ani</i> la inregistrare, va trebui sa urmati instructiunile primite, fie contul dumneavoastra trebuie sa fie activat. Unele forumuri obliga ca toti utilizatori noi sa isi activeze conturile , fie catre dumneavoastra personal, fie de catre administrator inainte de a va puteti autentifica. Cand v-ati inregistrat ati fi aflat daca este necesara activarea. Daca ati primit un email atunci urmati instructiunile. Daca nu, sunteti sigur ca ati specificat corect adresa de mail ? Unul din motivele pentru care activarea este folosita este pentru a preveni abuzul de catre utilizatori <i>pirat</i> anonimi. Daca sunteti sigur ca adresa de mail folosita este corecta atunci incercati sa contactati administratorul.");
$faq[] = array("M-am inregistrat cu ceva timp in urma dar nu ma mai pot autentifica?!", "Cele mai probabile motive sunt: ati folosit un nume de utilizator inexistent sau o parola gresita (verificati-va email-ul pe care l-ati primit cand v-ati inregistrat) sau administratorul a sters contul dumneavoastra dintr-un motiv sau altul. Daca motivul este al doilea, atunci poate nu ati scris nici un mesaj ? Se obisnuieste ca forumurile sa excluda periodic utilizatorii care nu au scris nimic pentru a reduce marimea bazei de date. Incercati sa va inregistrati din nou si sa va implicati in discutii.");


$faq[] = array("--","Preferinte si setari");
$faq[] = array("Cum imi schimb setarile?", "Toate setarile dumneavoastra (daca sunteti inregistrat) sunt stocate in baza de date. Pentru a le schimba folositi legatura <i>Profil</i> (in general afisata in partea superioara a paginilor dar nu intotdeauna). Acest lucru va va permite sa va schimbati toate setarile.");
$faq[] = array("Orele nu sunt corecte!", "Orele sunt aproape sigur corecte, dar ceea ce dumneavoastra s-ar putea sa vedeti ca toate orele afisate sunt intr-o zona cu fus orar diferit fata de cea in care sunteti. Daca este asa, ar trebui sa va schimbati setarea din profil pentru zona de fus orar in care sunteti, cum ar fi Bucuresti, Londra, Paris, etc. Va rugam, sa observati ca schimbarea zonei de fus orar, ca majoritatea setarilor, poate fi facuta doar de utilizatorii inregistrati. Deci daca nu sunteti inregistrat, acesta este un moment bun sa o faceti, daca imi permiteti jocul de cuvinte!");
$faq[] = array("Am schimbat zona de fus orar si ora tot este gresita!", "Daca sunteti sigur ca ati setat zona de fus orar corect si ora inca este diferita, cel mai probabil raspuns este ora de vara. Programul nu a fost gandit sa se plieze dupa schimbarile facute intre ora standard si cea de vara asa ca in timpul lunilor de vara ora poate fi diferita cu 60 de minute fata de cea locala reala.");
$faq[] = array("Limba mea nu este in lista!", "Cele mai probabile motive sunt fie ca administratorul nu a instalat limba dumneavoastra sau ca cineva nu a tradus inca acest program in limba dumneavoastra. Incercati sa il intrebati pe administrator daca poate instala limba de care aveti nevoie si daca nu exista, atunci sunteti liber sa creati o noua traducere. Mai multe informatii pot si gasite pe site-ul grupului phpBB. Urmariti legatura din partea inferioara  paginilor)");
$faq[] = array("Cum pot afisa o imagine sub numele meu de utilizator?", "Pot fi doua imagini sub numele de utilizator cand vizualizati mesajele. Prima este o imagine asociata cu rangul dumneavoastra, in general acestea luand forma de stele sau blocuri indicand cate mesaje ati scris sau statutul dumneavoastra pe forumuri. Sub aceasta ar putea fi o imagine mai mare cunoscuta sub numele de <i>avatar</i> (imagine asociata). Aceasta este, in general, unica sau personala fiecarui utilizator. Administratorul decide daca sa activeze imaginile asociate si au posibilitatea de a alege modalitatea prin care imaginile asociate pot fi folosite. Daca nu puteti folosi imaginile asociate, atunci aceasta este decizia administratorului si ar trebui sa-l intrebati pe acesta despre motivele care au dus la aceasta decizie (suntem siguri ca vor fi intemeiate!)");
$faq[] = array("Cum imi schimb rangul?", "In general nu puteti schimba direct rangurile (rangul apare direct sub numele dumneavoastra de utilizator, in mesaje si in profilul dumneavoastra, depinde de stilul folosit). Majoritatea forumurilor folosesc rangurile pentru a indica numarul de mesaje pe care le-ati scris si pentru a identifica anumiti utilizatori, cum ar fi moderatorii si administratorii. Va rugam, sa nu abuzati de forum scriind mesaje inutile doar pentru a va creste rangul, pentru ca probabil veti descoperi ca moderatorul sau administratorul va va scadea pur si simplu numarul de mesaje scrise.");
$faq[] = array("De ce cand folosesc legatura de email al unui utilizator imi cere sa ma autentific?", "Doar utilizatorii inregistrati pot trimite mesaje altor utilizatori prin formularul incorporat de mail (daca administratorul permite acest lucru). Acest lucru se intampla pentru a preveni folosirea malitioasa a sistemului de mesagerie de catre utilizatorii anonimi.");


$faq[] = array("--","Probleme de scriere/publicare a mesajelor");
$faq[] = array("Cum deschid un subiect in forum?", "Simplu. Apasati pe butonul specific, fie din forum, fie pe ecranul cu subiecte. Este posibil sa fie nevoie sa va inregistrati inainte de a scrie un mesaj. Facilitatile care va sunt disponibile sunt trecute in partea de jos a ecranului (de genul <i>Puteti crea un subiect nou in acest forum</i> )");
$faq[] = array("Cum pot modifica sau sterge un mesaj?", "In afara cazului in care sunteti administratorul forumului sau moderatorul, puteti modifica sau sterge doar mesajele dumneavoastra. Puteti modifica un mesaj (uneori doar pentru o scurta perioada dupa publicare) apasand butonul <i>modifica</i> pentru mesajul respectiv. Veti observa o mica sectiune de text sub mesaj cand reveniti la subiect. Aceasta arata de cate ori l-ati modificat. Aceasta va aparea doar daca nimeni nu v-a raspuns. De asemenea, nu va aparea daca moderatorii sau administratorii modifica mesajul (acestia ar trebui sa lase un mesaj in care sa spuna ce au modificat si de ce). Observati ca utilizatorii normali nu pot modifica un mesaj odata ce cineva a raspuns.");
$faq[] = array("Cum pot sa imi adaug semnatura la mesaj?", "Pentru a adauga o semnatura trebuie intai sa va creati una, lucru pe care il puteti face accesandu-va profilul. Odata ce semnatura este creata, puteti sa bifati optiunea <i>Adauga semnatura</i> de pe formularul de publicare pentru a va adauga semnatura. Puteti, de asemenea, sa va adaugati direct semnatura la toate mesajele bifand optiunea corespunzatoare din profil (puteti sa preveniti adaugarea semnaturii unor anumite mesaje daca stergeti bifa respectiva din formularul de publicare)");
$faq[] = array("Cum pot crea un sondaj?", "Este usor sa creati un sondaj. Cand creati un subiect nou (sau modificati primul mesaj al unui subiect, daca aveti permisiunea), ar trebui sa vedeti o sectiune <i>Adauga un chestionar</i> sub zona principala de publicare (daca nu vedeti acest lucru probabil ca nu aveti privilegiile de acces necesare pentru a crea sondaje). Ar trebui sa introduceti un titlu pentru chestionar si cel putin doua optiuni (pentru a alege o optiune scrieti intrebarea sondajului si apasati pe butonul <i>Adauga o optiune</i>). Puteti sa schimbati si valabilitatea sondajului, unde perioada 0 inseamna un sondaj perpetuu. Va exista si o limita a numarului de optiuni pe care il puteti folosi, specificata de catre administrator.");
$faq[] = array("Cum modific sau sterg un sondaj?", "Ca si in cazul mesajelor, sondajele pot fi modificate doar de autor, de moderator sau de un administrator. Pentru a modifica un sondaj efectuati un click pe primul mesaj din cadrul subiectului (acesta este intotdeauna asociat cu sondajul). Daca nimeni nu a votat, atunci poate fi sters sau modificat, insa daca cineva a votat deja, doar moderatorul sau administratorul poate modifica sau sterge sondajul. Acest lucru previne sabotarea sondajelor sau schimbarea inoportuna a optiunilor."); 
$faq[] = array("De ce nu pot sa accesez un forum?", "Unele forumuri pot fi limitate pentru anumiti utilizatori sau grupuri. Pentru a vedea, citi, publica, etc. este nevoie probabil de autorizatie speciala pe care o pot acorda doar moderatorul forumului si administratorul. Ar trebui sa luati legatura cu ei.");
$faq[] = array("De ce nu pot vota in sondaje?", "Doar utilizatorii inregistrati pot vota in sondaje (pentru a preveni falsificarea rezultatelor). Daca v-ati inregistrat si tot nu puteti vota atunci probabil ca nu aveti drepturile de acces necesare.");


$faq[] = array("--","Formatari si tipuri de mesaje");
$faq[] = array("Ce este codul BB?", "Codul BB este o implementare speciala a HTML-ului. Posibilitatea de a folosi codul BB este data de decizia administratorului (puteti dezactiva acest cod de la mesaj la mesaj din formularul de publicare). Codul BB este similar ca stil cu HTML-ul, balizele (tag-urile) sunt inchise in paranteze patrate [ si ] mai degraba decat &lt; si &gt; si ofera un control mai bun asupra a ce si cum se afiseaza. Pentru mai multe informatii despre codul BB, consultati ghidul care poate fi accesat de pe pagina de publicare.");
$faq[] = array("Pot folosi HTML?", "Acest lucru depinde de administrator, care are control complet. Daca va este permis sa il folositi, probabil ca veti descoperi ca doar cateva balize functioneaza. Aceasta este o masura de <i>securitate</i> pentru a preintampina abuzurile care ar duce la distrugerea asezarii in pagina sau ar cauza alte probleme. Daca HTML este activat, il puteti dezactiva de la mesaj la mesaj din formularul de publicare.");
$faq[] = array("Ce sunt <i>Zambetele</i>?", "Zambetele sau iconitele emotive sunt imagini mici care pot fi folosite pentru a exprima anumite sentimente folosind un cod scurt. Spre exemplu :) inseamna vesel , :( inseamna trist. Lista completa poate fi vazuta in formularul de publicare. Incercati totusi sa nu ii folositi prea mult pentru ca pot face un mesaj ilizibil si un moderator se poate hotari sa ii scoata din mesaj sau sa stearga mesajul cu totul.");
$faq[] = array("Pot publica imagini?", "Imaginile pot fi afisate in mesajele dumneavoastra. Cu toate acestea, nu exista nici o posibilitate in acest forum pentru incarcarea imaginilor direct in forum. De aceea, trebuie sa scrieti o legatura catre o imagine stocata pe un server accesibil publicului, cum ar fi <i>http://un.server.oarecare.ro/imaginea-mea.gif</i>. Nu puteti sa faceti legatura cu imagini de pe calculatorul dumneavoastra (doar daca este un server), nici cu imagini stocate in spatele unui mecanism de autentificare, cum ar fi casutele de mail, site-uri protejate cu parola, etc. Pentru a afisa imaginea, folositi fie baliza codului BB [img] sau cod HTML (daca este permis).");
$faq[] = array("Ce sunt anunturile?" ,"Anunturile deseori contin informatii importante si ar trebui sa le cititi cat de repede puteti. Anunturile apar in partea de sus a fiecarei pagini in forumul de care apartin. Daca puteti sau nu posta un anunt depinde de permisiunile necesare, care sunt stabilite de administrator.");             
$faq[] = array("Ce sunt subiectele lipicioase (sticky)?", "Subiectele lipicioase apar sub anunturi in forum si doar pe prima pagina. Deseori sunt destul de importante si ar trebui sa le cititi daca puteti. Ca si cu anunturile, administratorul alege ce permisiuni sunt necessre pentru pentru a le publica.");
$faq[] = array("Ce sunt subiectele blocate?", "Subiectele sunt blocate fie de catre moderator, fie de catre administrator. Nu puteti raspunde unui subiect blocat si orice chestionar continut este inchis automat. Subiectele pot fi inchise din mai multe motive.");


$faq[] = array("--","Nivele de utilizatori si grupuri");
$faq[] = array("Cine sunt administratorii?", "Administratorii sunt cei care au cel mai mare nivel de control asupra intregului forum. Acesti utilizatori controleaza toate detaliile forumurilor, incluzand permisiunile de acces, excluderea utilizatorilor, crearea grupurilor si a moderatorilor, etc. De asemenea, au capacitatea de a modera toate formurile.");
$faq[] = array("Cine sunt moderatorii?", "Moderatorii sunt persoane (sau grupuri de persoane) a caror menire este sa aiba grija de forumuri in mod constant. Au permisiunea de a modifica sau sterge mesajele si de a bloca, debloca, muta, sterge si imparti subiectele de discutie in forumurile pe care le modereaza. In mod general, moderatorii exista pentru a avea grija ca utilizatorii sa nu scrie \"pe langa subiect\" si sa nu scrie materiale abuzive sau ofensatoare.");
$faq[] = array("Ce sunt grupurile de utilizatori?", "Grupurile de utilizatori sunt o modalitate de grupare a utilizatorilor. Fiecare utilizator poate apartine mai multor grupuri (acest lucru difera de majoritatea celorlalte programe) si fiecare grup are drepturi de acces individuale. Acest lucru usureaza munca administratorilor daca doresc ca mai multi utilizatori sa modereze un forum sau sa le ofere acces intr-un forum privat.");
$faq[] = array("Cum pot sa fac parte dintr-un grup de utilizatori?", "Pentru a intra intr-un grup folositi legatura de <i>Grupuri de utilizatori</i> din partea de sus a paginii (acest lucru poate fi diferit de la pagina la pagina). Nu toate grupurile sunt deschise, unele sunt inchise si altele pot fi chiar ascunse. Daca grupul o permite, puteti sa cereti sa fiti inscris apasand butonul respectiv. Moderatorul va trebui sa va aprobe cererea. Este posibil sa va intrebe care sunt motivele pentru care va doriti sa intrati in grup. Va rugam sa nu hartuiti un moderator de grup daca va respinge cererea pentru ca are motive intemeiate.");
$faq[] = array("Cum pot deveni moderatorul unui grup de utilizatori?", "Grupurile de utilizatori sunt create de catre administrator si in acel moment este numit un moderator. Daca doriti sa creati un grup de utilizatori atunci primul lucru pe care trebuie sa il faceti este sa luati legatura cu administratorul.");


$faq[] = array("--","Mesaje private");
$faq[] = array("Nu pot trimite mesaje private!", "Exista trei posibile motive: nu sunteti inregistrat si/sau autentificat, administratorul nu a activat mesajele private pentru toti utilizatorii sau administratorul a restrictionat pentru dumneavoastra folosirea mesajelor private. In ultimul caz ar trebui sa il intrebati ce motive a avut.");
$faq[] = array("Tot primesc mesaje private nedorite!", "In viitor vom adauga o lista de persoane ale caror mesaje sa fie ignorate. Deocamdata daca primiti in continuare aceste mesaje anuntati administratorul, pentru ca el are posibilitatea de a opri un utilizator sa trimita mesaje private.");
$faq[] = array("Am primit spam-uri sau mesaje abuzive de la cineva din forum!", "Ne pare rau sa auzim acest lucru. Formularul pentru trimiterea unui mesaj include masuri de siguranta pentru a observa care utlizatori trimit astfel de mesaje. Ar trebui sa trimiteti administratorului o copie completa a mesajului primit, inclusiv antetul (acestea ofera detalii despre utilizatorul care a trimis mesajul). Astfel, el poate actiona.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","Intrebari despre phpBB 2");
$faq[] = array("Cine a scris acest program?", "Acest program (in forma nemodificata) este produs, lansat si aflat sub copyright-ul <a href=\"http://www.phpbb.com/\" target=\"_blank\">grupului phpBB</a>. Este disponibil sub Licenta Generala Publica GNU si poate fi distribuit liber; folositi legatura catre grup mai multe detalii.");
$faq[] = array("De ce nu este facilitatea X disponibila?", "Acest program a fost scris si licentiat de catre grupul phpBB. In cazul in care considerati ca o facilitate trebuie sa fie adaugata, va rugam sa vizitati site-ul phpBB.com si sa vedeti ce are de spus grupul phpBB. Va rugam sa nu publicati cereri de facilitati pe forumurile de la phpbb.com, pentru ca grupul phpBB foloseste sourceforge pentru a delega sarcinile legate de noile facilitati. Va rugam sa treceti prin aceste forumuri si sa vedeti, daca exista, care este pozitia noastra legata de o facilitate si sa urmati procedura explicata acolo.");
$faq[] = array("Cu cine iau legatura pentru probleme legate de abuzuri si/sau juridice legate de acest program?", "Ar trebui sa luati legatura cu administratorul forumului. Daca nu puteti sa faceti acest lucru, ar trebui sa incercati cu unul din moderatorii de forumuri si sa ii intrebati cu cine ati putea lua legatura. Daca tot nu primiti raspuns, ar trebui sa luati legatura cu posesorul domeniului dumneavoastra (efectuati o interogare whois) sau, daca acesta este pe un domeniu gratuit (yahoo, free.fr, etc.), conducerea sau departamentul pentru abuzuri. Grupul phpBB nu are absolut nici un fel de control si nu poate fi tras la raspundere pentru cum, unde sau de catre cine este folosit acest program. Este inutil sa luati legatura cu grupul phpBB pentru orice fel de probleme juridice care nu sunt legate direct de pagina phpbb.com sau de program in sine. Daca trimiteti un mesaj catre grupul phpBB despre orice folosire a unui tert a acestui program, nu asteptati un raspuns pentru ca nu veti primi.");

//
// This ends the FAQ entries
//

?>

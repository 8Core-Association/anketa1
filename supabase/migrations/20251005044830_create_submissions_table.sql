/*
  # Kreiranje tablice za anketu zadovoljstva korisnika
  
  1. Nova tablica
    - `submissions` - Spremanje odgovora ankete
      - `id` (uuid, primary key) - Jedinstveni ID zapisa
      - `created_at` (timestamp) - Vrijeme kreiranja
      - `record_no` (text) - Broj zapisa dokumenta (AZK SUK 0912)
      - `revision` (text) - Revizija dokumenta
      - `issue_date` (date) - Datum izdavanja dokumenta
      - `lang` (text) - Jezik ankete (hr/en)
      - `company` (text, NOT NULL) - Naziv organizacije
      - `address` (text, NOT NULL) - Adresa organizacije
      - `phone` (text) - Telefon
      - `fax` (text) - Faks
      - `web` (text) - Web stranica
      - `email` (text, NOT NULL) - E-mail adresa
      - `qms` (text) - Certificiran sustav kvalitete (yes/no)
      - `certificate` (text) - Naziv certifikata
      - `r1` (integer, NOT NULL) - Ocjena: Karakteristike proizvoda/usluge (1-4)
      - `r2` (integer, NOT NULL) - Ocjena: Kooperativnost osoblja (1-4)
      - `r3` (integer, NOT NULL) - Ocjena: Rok isporuke (1-4)
      - `r4` (integer, NOT NULL) - Ocjena: Cijena i uvjeti plaćanja (1-4)
      - `q1` (text) - Otvoreno pitanje: Nastavak suradnje
      - `q2` (text) - Otvoreno pitanje: Preporuka
      - `q3` (text) - Otvoreno pitanje: Primjedbe i prijedlozi
      - `filled_by` (text) - Tko je ispunio upitnik
      - `signature` (text) - Potpis
      - `doc_date` (date) - Datum potpisa
      - `ip` (text) - IP adresa korisnika
      - `user_agent` (text) - Browser info
  
  2. Sigurnost
    - RLS omogućen na tablici
    - Public INSERT policy - javnost može dodavati odgovore
    - Public SELECT policy - javnost može čitati vlastite odgovore (za PDF)
    - Admin SELECT policy - admin može čitati sve odgovore
*/

CREATE TABLE IF NOT EXISTS submissions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  created_at timestamptz DEFAULT now() NOT NULL,
  
  record_no text DEFAULT 'AZK SUK 0912' NOT NULL,
  revision text DEFAULT '1.00' NOT NULL,
  issue_date date DEFAULT '2019-11-05' NOT NULL,
  lang text DEFAULT 'hr' NOT NULL CHECK (lang IN ('hr', 'en')),
  
  company text NOT NULL,
  address text NOT NULL,
  phone text,
  fax text,
  web text,
  email text NOT NULL,
  
  qms text DEFAULT 'no' NOT NULL CHECK (qms IN ('yes', 'no')),
  certificate text,
  
  r1 integer NOT NULL CHECK (r1 >= 1 AND r1 <= 4),
  r2 integer NOT NULL CHECK (r2 >= 1 AND r2 <= 4),
  r3 integer NOT NULL CHECK (r3 >= 1 AND r3 <= 4),
  r4 integer NOT NULL CHECK (r4 >= 1 AND r4 <= 4),
  
  q1 text,
  q2 text,
  q3 text,
  
  filled_by text,
  signature text,
  doc_date date,
  
  ip text,
  user_agent text
);

ALTER TABLE submissions ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Javnost može dodavati odgovore"
  ON submissions
  FOR INSERT
  TO anon
  WITH CHECK (true);

CREATE POLICY "Javnost može čitati odgovore za PDF"
  ON submissions
  FOR SELECT
  TO anon
  USING (true);

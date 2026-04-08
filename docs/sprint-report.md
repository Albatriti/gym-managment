# Sprint 2 Report — Albatriti

## Çka Përfundova

### Feature e Re — Kërkim + Statistika
- ✅ Implementova metodën `kerko()` në MemberService — kërkim sipas emrit, mbiemrit dhe email-it
- ✅ Implementova metodën `statistika()` — total, active, expired, pending, përqindjet
- ✅ Rrjedha e plotë: UI → MemberService → MemberRepository → CSV

### Error Handling
- ✅ CSV mungon → krijohet automatikisht me header
- ✅ Input i gabuar → mesazh i qartë, programi vazhdon
- ✅ ID nuk ekziston → kthen null, shfaqet mesazh "Anëtari nuk u gjet"
- ✅ Try-catch në të gjitha metodat e Repository dhe Service
- ✅ Programi nuk crashon kurrë pavarësisht input-it

### Unit Tests
- ✅ 10 teste të implementuara në tests/MemberServiceTest.php
- ✅ Teste mbulojnë: listo, shto, gjej, kerko, statistika
- ✅ Teste kufitare: emër bosh, email jo valid, ID jo ekzistuese

## Çka Mbeti
- Integrimi i kërkimit live në UI me JavaScript (AJAX)
- Eksporti i statistikave në PDF

## Çka Mësova
- Si të implementoj try-catch në PHP për error handling të sigurt
- Si të përdor Reflection API në PHP për unit teste pa framework
- Rëndësia e validimit në shtresën Service dhe jo në UI
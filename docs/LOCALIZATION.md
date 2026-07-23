# Localization

User records persist `locale` and `timezone`. Schedule creation interprets unqualified dates
in the user's timezone and stores UTC. Notification content uses the saved user locale.

The marketing UI supports Arabic and English. The authenticated app supports Arabic and
English with RTL/LTR. Spanish and Turkish backend locale files and complete product copy are
not implemented.

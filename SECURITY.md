# Security Policy

## ⚠️ Important Notice

This application is **INTENTIONALLY VULNERABLE** and designed for:
- Educational purposes
- Security testing and training
- Penetration testing practice
- Vulnerability research

## DO NOT Use This Application For:

❌ Production environments  
❌ Storing real or sensitive data  
❌ Public-facing websites  
❌ Any critical infrastructure

## Known Vulnerabilities

This application contains the following **intentional** security flaws:

### CRITICAL

1. **SQL Injection** - Multiple endpoints (login, search, CRUD operations)
2. **No CSRF Protection** - All forms lack CSRF tokens
3. **Weak Session Management** - Insecure session configuration
4. **Information Disclosure** - Verbose error messages in debug mode

### HIGH

5. **No Input Validation** - User input not sanitized
6. **Weak Password Storage** - MD5 hashing available
7. **Path Traversal** - File upload without validation
8. **No Rate Limiting** - Brute force attacks possible

### MEDIUM

9. **XSS Vulnerabilities** - Output not properly escaped
10. **Insecure Direct Object References** - ID-based access without authorization

## Responsible Disclosure

If you discover **additional** vulnerabilities not listed above:

1. Do NOT exploit them maliciously
2. Document your findings
3. Report to the project maintainer
4. Allow time for documentation update

## Legal Notice

- This software is provided "AS IS" for educational purposes only
- The authors are NOT responsible for misuse
- Using this software against systems without authorization is ILLEGAL
- Only use in isolated, controlled environments

## Safe Usage Guidelines

✅ Run in isolated VM or container  
✅ Use non-routable IP addresses  
✅ Do not expose to the Internet  
✅ Use for authorized testing only  
✅ Document all findings for learning

## Remediation Resources

For learning how to fix these vulnerabilities:
- See `/docs/SQL_INJECTION_GUIDE.md`
- Review OWASP Top 10: https://owasp.org/Top10/
- PHP Security Guide: https://phptherightway.com/#security

---

**Remember:** With great power comes great responsibility. Use this tool ethically and legally.

/**
 * Plugin Vite pour les vérifications de sécurité
 * Analyse le code pendant la construction pour détecter les vulnérabilités potentielles
 */
export function securityPlugin(options = {}) {
  const {
    enableXssCheck = true,
    enableSqlInjectionCheck = true,
    enableUnsafeApiCheck = true,
    enableDependencyCheck = true,
    failOnWarning = false,
    excludePatterns = [/node_modules/, /\.min\.js$/]
  } = options

  let warnings = []
  let errors = []

  // Patterns de sécurité dangereux
  const securityPatterns = {
    xss: [
      {
        pattern: /innerHTML\s*=/g,
        message: 'Usage d\'innerHTML détecté - risque XSS. Utilisez textContent ou des méthodes sécurisées.',
        severity: 'warning'
      },
      {
        pattern: /document\.write\s*\(/g,
        message: 'Usage de document.write() détecté - risque XSS.',
        severity: 'error'
      },
      {
        pattern: /eval\s*\(/g,
        message: 'Usage d\'eval() détecté - risque d\'injection de code.',
        severity: 'error'
      },
      {
        pattern: /new\s+Function\s*\(/g,
        message: 'Usage de new Function() détecté - risque d\'injection de code.',
        severity: 'error'
      },
      {
        pattern: /dangerouslySetInnerHTML/g,
        message: 'Usage de dangerouslySetInnerHTML détecté - risque XSS.',
        severity: 'warning'
      }
    ],
    
    sqlInjection: [
      {
        pattern: /query\s*\(\s*[`'"].*?\+.*?[`'"]\s*\)/g,
        message: 'Concaténation de chaîne dans une requête SQL détectée - risque d\'injection SQL.',
        severity: 'error'
      },
      {
        pattern: /\$\{.*?\}\s*FROM\s+/gi,
        message: 'Interpolation de template dans une requête SQL - risque d\'injection.',
        severity: 'error'
      }
    ],
    
    unsafeApi: [
      {
        pattern: /localStorage\.setItem\s*\(\s*[`'"].*?(token|password|secret).*?[`'"]/gi,
        message: 'Stockage de données sensibles dans localStorage détecté.',
        severity: 'warning'
      },
      {
        pattern: /sessionStorage\.setItem\s*\(\s*[`'"].*?(token|password|secret).*?[`'"]/gi,
        message: 'Stockage de données sensibles dans sessionStorage détecté.',
        severity: 'warning'
      },
      {
        pattern: /console\.log\s*\(.*?(password|token|secret|key)/gi,
        message: 'Log de données sensibles détecté.',
        severity: 'error'
      },
      {
        pattern: /http:\/\//g,
        message: 'URL HTTP non sécurisée détectée - utilisez HTTPS.',
        severity: 'warning'
      }
    ],
    
    general: [
      {
        pattern: /Math\.random\(\)/g,
        message: 'Math.random() n\'est pas cryptographiquement sûr - utilisez crypto.getRandomValues().',
        severity: 'info'
      },
      {
        pattern: /btoa\s*\(/g,
        message: 'btoa() pour l\'encodage de données sensibles n\'est pas sécurisé.',
        severity: 'warning'
      }
    ]
  }

  // Dépendances potentiellement dangereuses
  const unsafeDependencies = [
    'eval',
    'vm2',
    'serialize-javascript',
    'node-serialize',
    'funcster'
  ]

  function analyzeFile(code, filePath) {
    const issues = []

    // Ignorer les fichiers exclus
    if (excludePatterns.some(pattern => pattern.test(filePath))) {
      return issues
    }

    // Vérifier les patterns XSS
    if (enableXssCheck) {
      securityPatterns.xss.forEach(({ pattern, message, severity }) => {
        const matches = [...code.matchAll(pattern)]
        matches.forEach(match => {
          issues.push({
            file: filePath,
            line: getLineNumber(code, match.index),
            column: getColumnNumber(code, match.index),
            message,
            severity,
            type: 'XSS',
            code: getCodeSnippet(code, match.index)
          })
        })
      })
    }

    // Vérifier les patterns d'injection SQL
    if (enableSqlInjectionCheck) {
      securityPatterns.sqlInjection.forEach(({ pattern, message, severity }) => {
        const matches = [...code.matchAll(pattern)]
        matches.forEach(match => {
          issues.push({
            file: filePath,
            line: getLineNumber(code, match.index),
            column: getColumnNumber(code, match.index),
            message,
            severity,
            type: 'SQL_INJECTION',
            code: getCodeSnippet(code, match.index)
          })
        })
      })
    }

    // Vérifier les APIs non sécurisées
    if (enableUnsafeApiCheck) {
      securityPatterns.unsafeApi.forEach(({ pattern, message, severity }) => {
        const matches = [...code.matchAll(pattern)]
        matches.forEach(match => {
          issues.push({
            file: filePath,
            line: getLineNumber(code, match.index),
            column: getColumnNumber(code, match.index),
            message,
            severity,
            type: 'UNSAFE_API',
            code: getCodeSnippet(code, match.index)
          })
        })
      })
    }

    // Vérifier les patterns généraux
    securityPatterns.general.forEach(({ pattern, message, severity }) => {
      const matches = [...code.matchAll(pattern)]
      matches.forEach(match => {
        issues.push({
          file: filePath,
          line: getLineNumber(code, match.index),
          column: getColumnNumber(code, match.index),
          message,
          severity,
          type: 'GENERAL',
          code: getCodeSnippet(code, match.index)
        })
      })
    })

    return issues
  }

  function getLineNumber(code, index) {
    return code.substring(0, index).split('\n').length
  }

  function getColumnNumber(code, index) {
    const lines = code.substring(0, index).split('\n')
    return lines[lines.length - 1].length + 1
  }

  function getCodeSnippet(code, index, contextLines = 2) {
    const lines = code.split('\n')
    const lineNumber = getLineNumber(code, index)
    const start = Math.max(0, lineNumber - contextLines - 1)
    const end = Math.min(lines.length, lineNumber + contextLines)
    
    return lines.slice(start, end).map((line, i) => {
      const actualLineNumber = start + i + 1
      const marker = actualLineNumber === lineNumber ? '>' : ' '
      return `${marker} ${actualLineNumber.toString().padStart(3, ' ')}: ${line}`
    }).join('\n')
  }

  function checkPackageJson(packagePath) {
    try {
      const packageJson = JSON.parse(fs.readFileSync(packagePath, 'utf8'))
      const dependencies = {
        ...packageJson.dependencies,
        ...packageJson.devDependencies
      }

      const unsafeFound = []
      Object.keys(dependencies).forEach(dep => {
        if (unsafeDependencies.includes(dep)) {
          unsafeFound.push({
            dependency: dep,
            version: dependencies[dep],
            message: `Dépendance potentiellement dangereuse détectée: ${dep}`
          })
        }
      })

      return unsafeFound
    } catch (error) {
      return []
    }
  }

  function formatIssues(issues) {
    if (issues.length === 0) return ''

    let output = '\n🛡️  Rapport de sécurité Koumbaya\n'
    output += '═'.repeat(50) + '\n\n'

    const grouped = issues.reduce((acc, issue) => {
      if (!acc[issue.type]) acc[issue.type] = []
      acc[issue.type].push(issue)
      return acc
    }, {})

    Object.entries(grouped).forEach(([type, typeIssues]) => {
      output += `📁 ${type} (${typeIssues.length} issue${typeIssues.length > 1 ? 's' : ''})\n`
      output += '─'.repeat(30) + '\n'

      typeIssues.forEach(issue => {
        const icon = issue.severity === 'error' ? '❌' : 
                    issue.severity === 'warning' ? '⚠️' : 'ℹ️'
        
        output += `${icon} ${issue.file}:${issue.line}:${issue.column}\n`
        output += `   ${issue.message}\n`
        output += `   Code:\n${issue.code.split('\n').map(l => '   ' + l).join('\n')}\n\n`
      })
    })

    const errorCount = issues.filter(i => i.severity === 'error').length
    const warningCount = issues.filter(i => i.severity === 'warning').length
    
    output += `\n📊 Résumé: ${errorCount} erreurs, ${warningCount} avertissements\n`
    
    if (errorCount > 0) {
      output += '\n🚨 Des vulnérabilités critiques ont été détectées!\n'
    }

    return output
  }

  return {
    name: 'koumbaya-security',
    
    buildStart() {
      warnings = []
      errors = []
      console.log('🛡️  Analyse de sécurité Koumbaya démarrée...')
    },

    transform(code, id) {
      if (id.includes('node_modules')) return
      if (!/\.(vue|js|ts|jsx|tsx)$/.test(id)) return

      const issues = analyzeFile(code, id)
      
      issues.forEach(issue => {
        if (issue.severity === 'error') {
          errors.push(issue)
        } else {
          warnings.push(issue)
        }
      })

      return null
    },

    buildEnd() {
      const allIssues = [...errors, ...warnings]
      
      if (allIssues.length > 0) {
        const report = formatIssues(allIssues)
        console.log(report)

        // Écrire le rapport dans un fichier
        const fs = require('fs')
        const path = require('path')
        const reportPath = path.join(process.cwd(), 'security-report.txt')
        fs.writeFileSync(reportPath, report)
        console.log(`📄 Rapport de sécurité sauvegardé: ${reportPath}`)
      }

      // Vérifier les dépendances
      if (enableDependencyCheck) {
        const packagePath = path.join(process.cwd(), 'package.json')
        const unsafeDeps = checkPackageJson(packagePath)
        
        if (unsafeDeps.length > 0) {
          console.log('\n⚠️  Dépendances potentiellement dangereuses:')
          unsafeDeps.forEach(dep => {
            console.log(`   - ${dep.dependency}@${dep.version}: ${dep.message}`)
          })
        }
      }

      // Échouer le build si des erreurs critiques sont trouvées
      if (errors.length > 0 && (failOnWarning || errors.some(e => e.severity === 'error'))) {
        throw new Error(`Build échoué: ${errors.length} vulnérabilités critiques détectées`)
      }

      if (allIssues.length === 0) {
        console.log('✅ Aucune vulnérabilité détectée!')
      } else {
        console.log(`🛡️  Analyse terminée: ${errors.length} erreurs, ${warnings.length} avertissements`)
      }
    }
  }
}
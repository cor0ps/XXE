# XXE
XXE Injection即XML External Entity Injection,也就是XML外部实体注入攻击。
## 1 XXE
本质上xxe的漏洞都是因为对xml解析时允许引用外部实体，从而导致读取任意文件、探测内网端口、攻击内网网站、发起DoS拒绝服务攻击、执行系统命令等。
### 1.1 DocumentBuilderFactory
错误示例：
```
// DOM 解析器的工厂实例
DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
//DOM 工厂获得 DOM 解析器
DocumentBuilder dbr = dbf.newDocumentBuilder();
//解析Document
org.w3c.dom.Document doc = dbr.parse(in);
// 得到xml根元素
org.w3c.dom.Element root = doc.getDocumentElement();
praseElement(root);

```
正确示例：
```java
// DOM 解析器的工厂实例
DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
dbf.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
dbf.setFeature("http://xml.org/sax/features/external-general-entities", false);
dbf.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
//DOM 工厂获得 DOM 解析器
DocumentBuilder dbr = dbf.newDocumentBuilder();
//解析Document
org.w3c.dom.Document doc = dbr.parse(in);
// 得到xml根元素
org.w3c.dom.Element root = doc.getDocumentElement();
org.w3c.dom.NodeList elementList = root.getChildNodes();
// 遍历所有子节点
praseElement(root);
```
### 1.2 SAXReader
默认情况下，出现XXE
错误示例:
```
SAXReader saxReader = new SAXReader();
//生成document文件
Document document = saxReader.read(in);
// 得到xml根元素
Element root = document.getRootElement();
// 得到根元素的所有子节点
List<Element> elementList = root.elements();
```
正确示例：
```java
SAXReader saxReader = new SAXReader();
saxReader.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
saxReader.setFeature("http://xml.org/sax/features/external-general-entities", false);
saxReader.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
Document document = saxReader.read(in);
Element root = document.getRootElement();
List<Element> elementList = root.elements();
```
### 1.3 XMLReader
错误示例：
```
XMLReader reader = XMLReaderFactory.createXMLReader();
reader.parse(new InputSource(request));
```
正确示例：
```java
XMLReader reader = XMLReaderFactory.createXMLReader();
reader.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
reader.setFeature("http://xml.org/sax/features/external-general-entities", false);
reader.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
reader.parse(new InputSource(InputSource));
```

### 1.4 SAXParserFactory
同样，默认情况下，存在XXE
错误示例：
```
SAXParserFactory spf = SAXParserFactory.newInstance();
SAXParser parser = spf.newSAXParser();
```
正确示例：
```java
SAXParserFactory spf = SAXParserFactory.newInstance();
spf.setFeature("http://xml.org/sax/features/external-general-entities", false);
spf.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
spf.setFeature("http://apache.org/xml/features/nonvalidating/load-external-dtd", false);
SAXParser parser = spf.newSAXParser();
```

### 1.5 SAXBuilder
这个库貌似使用得不是很多。SAXBuilder如果使用默认配置就会触发XXE漏洞；
错误示例：
```
SAXBuilder builder = new SAXBuilder();
Document doc = builder.build(InputSource);
```
正确示例：
```java
SAXBuilder builder = new SAXBuilder();
builder.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
builder.setFeature("http://xml.org/sax/features/external-general-entities", false);
builder.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
builder.setFeature("http://apache.org/xml/features/nonvalidating/load-external-dtd", false);
Document doc = builder.build(InputSource);
```
### 1.6 SchemaFactory
默认情况也会出现XXE
错误示例：
```
SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
Schema schema = factory.newSchema(Source);
```
正确示例：
```java
SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
factory.setProperty(XMLConstants.ACCESS_EXTERNAL_DTD, "");
factory.setProperty(XMLConstants.ACCESS_EXTERNAL_SCHEMA, "");
Schema schema = factory.newSchema(Source);
```
### 1.7 SAXTransformerFactory
正确示例：
```java
SAXTransformerFactory sf = SAXTransformerFactory.newInstance();
sf.setAttribute(XMLConstants.ACCESS_EXTERNAL_DTD, "");
sf.setAttribute(XMLConstants.ACCESS_EXTERNAL_STYLESHEET, "");
sf.newXMLFilter(Source);
```
Note :XMLConstants requires JAXP 1.5, which was added to Java in 7u40 and Java 8:

- javax.xml.XMLConstants.ACCESS_EXTERNAL_DTD
- javax.xml.XMLConstants.ACCESS_EXTERNAL_SCHEMA
- javax.xml.XMLConstants.ACCESS_EXTERNAL_STYLESHEET

### 1.8 TransformerFactory
错误示例：
```
TransformerFactory tf = TransformerFactory.newInstance();
StreamSource source = new StreamSource(InputSource);
tf.newTransformer().transform(source, new DOMResult());
```
正确示例;
```java
TransformerFactory tf = TransformerFactory.newInstance();
tf.setAttribute(XMLConstants.ACCESS_EXTERNAL_DTD, "");
tf.setAttribute(XMLConstants.ACCESS_EXTERNAL_STYLESHEET, "");
StreamSource source = new StreamSourceInputSource);
tf.newTransformer().transform(source, new DOMResult());
```



### 1.9 Digester
Digester本来仅仅是Jakarta Struts中的一个工具，用于处理struts-config.xml配置文件。
错误示例：
```
Digester digester = new Digester();
digester.parse(new StringReader(xml)); 
```
正确示例：
```java
Digester digester = new Digester();
digester.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
digester.setFeature("http://xml.org/sax/features/external-general-entities", false);
digester.setFeature("http://xml.org/sax/features/external-parameter-entities", false);
digester.parse(new StringReader(xml));  // parse xml
```
### 1.10 javax.xml.validation.Validator

错误示例：
```
SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
Schema schema = factory.newSchema();
Validator validator = schema.newValidator();
StreamSource source = new StreamSource(InputSource);
validator.validate(source);
```
正常示例：
```java
SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
factory.setProperty(XMLConstants.ACCESS_EXTERNAL_DTD,"")
factory.setProperty(XMLConstants.ACCESS_EXTERNAL_SCHEMA,"")
Validator validator = schema.newValidator();
StreamSource source = new StreamSource(InputSource);
validator.validate(source);
```

https://blog.csdn.net/raintungli/article/details/53486383
